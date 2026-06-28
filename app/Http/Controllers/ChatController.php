<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    /**
     * Serves the Client Chat Page.
     */
    public function ClientChatPage()
    {
        $clientSession = Session::get('user');
        if (!$clientSession) {
            return redirect()->route('client.login.page')->with('error', 'Please login first.');
        }

        // Fetch fresh client data
        $client = DB::table('clients')->where('id', $clientSession->id)->first();
        if (!$client) {
            Session::flush();
            return redirect()->route('client.login.page')->with('error', 'Account not found.');
        }

        $area = DB::table('areas')->where('id', $client->area_id)->first();

        // Get allowed chat contacts
        $collector = null;
        $secretary = null;
        $admin = DB::table('admins')->first(); // Fallback to first admin

        if ($area) {
            $collector = DB::table('collectors')
                ->where('id', $area->collector_id)
                ->first();

            $secretary = DB::table('secretaries')
                ->where('id', $area->secretary_id)
                ->first();
        }

        return view('client.chat.index', compact('client', 'area', 'collector', 'secretary', 'admin'));
    }

    /**
     * Serves the Staff Chat Dashboard (for Admins, Secretaries, and Collectors).
     */
    public function StaffChatPage()
    {
        $user = Session::get('user');
        $role = Session::get('role');

        if (!$user || !$role) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        if ($role === 'admin') {
            return view('admin.chat.index', compact('user', 'role'));
        } elseif ($role === 'secretary') {
            return view('secretary.chat.index', compact('user', 'role'));
        } elseif ($role === 'collector') {
            return view('collector.chat.index', compact('user', 'role'));
        }

        return abort(403);
    }

    /**
     * Fetch active conversations list.
     */
    public function getConversations()
    {
        $user = Session::get('user');
        $role = Session::get('role');

        if (!$user || !$role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($role === 'client') {
            // Client: Get all conversations they are part of
            $conversations = DB::table('chat_conversations as cc')
                ->where('cc.client_id', $user->id)
                ->select('cc.*')
                ->orderBy('cc.last_message_at', 'desc')
                ->get()
                ->map(function ($convo) {
                    $staffName = 'ULC Support';
                    if ($convo->staff_type === 'admin') {
                        $staff = DB::table('admins')->where('id', $convo->staff_id)->first();
                        $staffName = $staff ? $staff->fullname : 'Admin Support';
                    } elseif ($convo->staff_type === 'secretary') {
                        $staff = DB::table('secretaries')->where('id', $convo->staff_id)->first();
                        $staffName = $staff ? $staff->fullname : 'Office Secretary';
                    } elseif ($convo->staff_type === 'collector') {
                        $staff = DB::table('collectors')->where('id', $convo->staff_id)->first();
                        $staffName = $staff ? $staff->fullname : 'Area Collector';
                    }

                    $convo->recipient_name = $staffName;
                    
                    // Fetch latest message
                    $latestMsg = DB::table('chat_messages')
                        ->where('conversation_id', $convo->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $convo->latest_message = $latestMsg ? $latestMsg->message : '';
                    $convo->unread_count = DB::table('chat_messages')
                        ->where('conversation_id', $convo->id)
                        ->where('sender_type', '!=', 'client')
                        ->where('is_read', 0)
                        ->count();

                    return $convo;
                });

            return response()->json($conversations);
        } else {
            // Staff members: admin, secretary, or collector
            $query = DB::table('chat_conversations as cc')
                ->join('clients as c', 'cc.client_id', '=', 'c.id')
                ->leftJoin('areas as a', 'c.area_id', '=', 'a.id')
                ->select('cc.*', 'c.fullname as client_name', 'c.phone as client_phone', 'a.areas_name', 'a.location_name');

            if ($role === 'secretary') {
                // Filter: Only conversations assigned to this secretary,
                // and the client must be inside one of this secretary's assigned areas
                $assignedAreaIds = DB::table('areas')
                    ->where('secretary_id', $user->id)
                    ->pluck('id')
                    ->toArray();

                $query->where('cc.staff_type', 'secretary')
                    ->where('cc.staff_id', $user->id)
                    ->whereIn('c.area_id', $assignedAreaIds);
            } elseif ($role === 'collector') {
                // Filter: Only conversations assigned to this collector,
                // and the client must be inside one of this collector's assigned areas
                $assignedAreaIds = DB::table('areas')
                    ->where('collector_id', $user->id)
                    ->pluck('id')
                    ->toArray();

                $query->where('cc.staff_type', 'collector')
                    ->where('cc.staff_id', $user->id)
                    ->whereIn('c.area_id', $assignedAreaIds);
            } elseif ($role === 'admin') {
                // Admins see all conversations
                $query->where('cc.staff_type', 'admin');
            } else {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $conversations = $query->orderBy('cc.last_message_at', 'desc')->get()->map(function ($convo) use ($role) {
                // Fetch latest message
                $latestMsg = DB::table('chat_messages')
                    ->where('conversation_id', $convo->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $convo->latest_message = $latestMsg ? $latestMsg->message : '';
                $convo->unread_count = DB::table('chat_messages')
                    ->where('conversation_id', $convo->id)
                    ->where('sender_type', 'client')
                    ->where('is_read', 0)
                    ->count();

                return $convo;
            });

            return response()->json($conversations);
        }
    }

    /**
     * Retrieve messages for a given conversation.
     */
    public function getMessages($conversationId)
    {
        $user = Session::get('user');
        $role = Session::get('role');

        if (!$user || !$role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $convo = DB::table('chat_conversations')->where('id', $conversationId)->first();
        if (!$convo) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // --- STRICT CONFIDENTIALITY & ACCESS RULES ---
        if ($role === 'client') {
            if ($convo->client_id !== $user->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        } elseif ($role === 'secretary') {
            if ($convo->staff_type !== 'secretary' || $convo->staff_id !== $user->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            // Verify client is in secretary's areas
            $clientArea = DB::table('clients')->where('id', $convo->client_id)->value('area_id');
            $isAssigned = DB::table('areas')
                ->where('id', $clientArea)
                ->where('secretary_id', $user->id)
                ->exists();
            if (!$isAssigned) {
                return response()->json(['message' => 'Forbidden: client area mismatch'], 403);
            }
        } elseif ($role === 'collector') {
            if ($convo->staff_type !== 'collector' || $convo->staff_id !== $user->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            // Verify client is in collector's areas
            $clientArea = DB::table('clients')->where('id', $convo->client_id)->value('area_id');
            $isAssigned = DB::table('areas')
                ->where('id', $clientArea)
                ->where('collector_id', $user->id)
                ->exists();
            if (!$isAssigned) {
                return response()->json(['message' => 'Forbidden: client area mismatch'], 403);
            }
        } elseif ($role === 'admin') {
            if ($convo->staff_type !== 'admin') {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        } else {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Mark recipient messages as read
        if ($role === 'client') {
            DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_type', '!=', 'client')
                ->update(['is_read' => 1]);
        } else {
            DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_type', 'client')
                ->update(['is_read' => 1]);
        }

        $messages = DB::table('chat_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Send a chat message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|integer',
            'receiver_type' => 'required_without:conversation_id|string|in:admin,secretary,collector',
            'receiver_id' => 'required_without:conversation_id|integer',
            'message' => 'required|string|max:5000',
        ]);

        $user = Session::get('user');
        $role = Session::get('role');

        if (!$user || !$role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversationId = $request->input('conversation_id');
        $messageText = $request->input('message');

        if (!$conversationId) {
            // Initiate new conversation (must be initiated by client)
            if ($role !== 'client') {
                return response()->json(['message' => 'Only clients can initiate new conversations.'], 403);
            }

            $receiverType = $request->input('receiver_type');
            $receiverId = (int)$request->input('receiver_id');

            // --- STRICT RECIPIENT VALIDATION FOR CLIENTS ---
            $client = DB::table('clients')->where('id', $user->id)->first();
            if (!$client) {
                return response()->json(['message' => 'Client profile not found.'], 404);
            }

            if ($receiverType === 'admin') {
                $recipientExists = DB::table('admins')->where('id', $receiverId)->exists();
            } elseif ($receiverType === 'secretary') {
                // Verify secretary is assigned to client's area
                $recipientExists = DB::table('areas')
                    ->where('id', $client->area_id)
                    ->where('secretary_id', $receiverId)
                    ->exists();
            } elseif ($receiverType === 'collector') {
                // Verify collector is assigned to client's area
                $recipientExists = DB::table('areas')
                    ->where('id', $client->area_id)
                    ->where('collector_id', $receiverId)
                    ->exists();
            } else {
                return response()->json(['message' => 'Invalid staff receiver type.'], 400);
            }

            if (!$recipientExists) {
                return response()->json(['message' => 'Recipient is not assigned to your area location.'], 403);
            }

            // Find or Create conversation
            $conversationId = DB::table('chat_conversations')->insertGetId([
                'client_id' => $user->id,
                'staff_type' => $receiverType,
                'staff_id' => $receiverId,
                'last_message_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Query conversation and validate ownership/access
            $convo = DB::table('chat_conversations')->where('id', $conversationId)->first();
            if (!$convo) {
                return response()->json(['message' => 'Conversation not found'], 404);
            }

            // Verify access rights
            if ($role === 'client') {
                if ($convo->client_id !== $user->id) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }
            } elseif ($role === 'secretary') {
                if ($convo->staff_type !== 'secretary' || $convo->staff_id !== $user->id) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }
            } elseif ($role === 'collector') {
                if ($convo->staff_type !== 'collector' || $convo->staff_id !== $user->id) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }
            } elseif ($role === 'admin') {
                if ($convo->staff_type !== 'admin') {
                    return response()->json(['message' => 'Forbidden'], 403);
                }
            }
        }

        // Insert message
        DB::table('chat_messages')->insert([
            'conversation_id' => $conversationId,
            'sender_type' => $role,
            'sender_id' => $user->id,
            'message' => $messageText,
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update conversation last message timestamp
        DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->update([
                'last_message_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
    }
}
