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
            'receiver_type' => 'required_without:conversation_id|string|in:admin,secretary,collector,bot',
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
            } elseif ($receiverType === 'bot') {
                $recipientExists = ($receiverId === 0);
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

        // Check if the recipient of the message is the AI bot
        $convo = DB::table('chat_conversations')->where('id', $conversationId)->first();
        if ($convo && $convo->staff_type === 'bot') {
            // Process AI Bot answer
            $botResponse = $this->getBotAnswer($user->id, $messageText);
            
            // Insert bot message
            DB::table('chat_messages')->insert([
                'conversation_id' => $conversationId,
                'sender_type' => 'bot',
                'sender_id' => 0,
                'message' => $botResponse,
                'is_read' => 1,
                'created_at' => now()->addSecond(),
                'updated_at' => now()->addSecond(),
            ]);

            // Update conversation last message timestamp
            DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->update([
                    'last_message_at' => now()->addSecond(),
                    'updated_at' => now()
                ]);
        } else {
            // Update conversation last message timestamp
            DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->update([
                    'last_message_at' => now(),
                    'updated_at' => now()
                ]);
        }

        return response()->json(['message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
    }

    /**
     * Process bot quick-query logic and return response in Tagalog.
     */
    private function getBotAnswer($clientId, $messageText)
    {
        $messageText = strtolower($messageText);

        $client = DB::table('clients')->where('id', $clientId)->first();
        $area = $client ? DB::table('areas')->where('id', $client->area_id)->first() : null;
        $collector = $area ? DB::table('collectors')->where('id', $area->collector_id)->first() : null;
        $secretary = $area ? DB::table('secretaries')->where('id', $area->secretary_id)->first() : null;

        // Query: Payments This Week (Must be checked before 'ngayon' to avoid conflict)
        if (strpos($messageText, 'ngayong linggo') !== false || (strpos($messageText, 'linggo') !== false && strpos($messageText, 'ngayon') !== false)) {
            $startOfWeek = now()->timezone('Asia/Manila')->startOfWeek()->format('Y-m-d');
            $endOfWeek = now()->timezone('Asia/Manila')->endOfWeek()->format('Y-m-d');

            $paymentsThisWeek = DB::table('clients_payments as cp')
                ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
                ->where('cp.client_id', $clientId)
                ->whereBetween('cp.due_date', [$startOfWeek, $endOfWeek])
                ->select('cp.*', 'col.fullname as collected_by_name')
                ->orderBy('cp.due_date', 'asc')
                ->get();

            $totalPaid = $paymentsThisWeek->sum('collection');

            if ($paymentsThisWeek->count() > 0) {
                $lines = "";
                foreach ($paymentsThisWeek as $p) {
                    $lines .= "🔹 **Petsa:** " . date('M d, Y', strtotime($p->due_date)) . "\n" .
                              "   * **Halaga:** P" . number_format($p->collection, 2) . "\n" .
                              "   * **Reference No:** " . $p->reference_number . "\n" .
                              "   * **Kinolekta ni:** " . ($p->collected_by_name ?? 'Collector') . "\n\n";
                }

                return "📅 **Breakdown ng iyong mga hulog ngayong linggo (" . date('M d', strtotime($startOfWeek)) . " - " . date('M d, Y', strtotime($endOfWeek)) . "):**\n\n" .
                    $lines .
                    "💵 **Kabuong Hulog Ngayong Linggo:** P" . number_format($totalPaid, 2) . "\n\n" .
                    "Salamat sa iyong patuloy na pagbabayad! 👍";
            } else {
                return "❌ **Walang nakitang hulog ngayong linggo (" . date('M d', strtotime($startOfWeek)) . " - " . date('M d, Y', strtotime($endOfWeek)) . "):**\n\n" .
                    "Wala pa kaming natatanggap na tala ng iyong bayad para sa linggong ito sa aming system.\n\n" .
                    "Kung ikaw ay may naibigay na bayad sa iyong Area Collector, mangyaring hintayin na ma-encode ito sa system.";
            }
        }

        // Query: Payments Last Week (Must be checked before 'kahapon' or 'ngayon' to avoid conflict)
        if (strpos($messageText, 'nakaraang linggo') !== false || strpos($messageText, 'nakalipas na linggo') !== false || (strpos($messageText, 'linggo') !== false && (strpos($messageText, 'nakaraan') !== false || strpos($messageText, 'nakalipas') !== false))) {
            $startOfLastWeek = now()->timezone('Asia/Manila')->subWeek()->startOfWeek()->format('Y-m-d');
            $endOfLastWeek = now()->timezone('Asia/Manila')->subWeek()->endOfWeek()->format('Y-m-d');

            $paymentsLastWeek = DB::table('clients_payments as cp')
                ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
                ->where('cp.client_id', $clientId)
                ->whereBetween('cp.due_date', [$startOfLastWeek, $endOfLastWeek])
                ->select('cp.*', 'col.fullname as collected_by_name')
                ->orderBy('cp.due_date', 'asc')
                ->get();

            $totalPaid = $paymentsLastWeek->sum('collection');

            if ($paymentsLastWeek->count() > 0) {
                $lines = "";
                foreach ($paymentsLastWeek as $p) {
                    $lines .= "🔹 **Petsa:** " . date('M d, Y', strtotime($p->due_date)) . "\n" .
                              "   * **Halaga:** P" . number_format($p->collection, 2) . "\n" .
                              "   * **Reference No:** " . $p->reference_number . "\n" .
                              "   * **Kinolekta ni:** " . ($p->collected_by_name ?? 'Collector') . "\n\n";
                }

                return "📅 **Breakdown ng iyong mga hulog nakaraang linggo (" . date('M d', strtotime($startOfLastWeek)) . " - " . date('M d, Y', strtotime($endOfLastWeek)) . "):**\n\n" .
                    $lines .
                    "💵 **Kabuong Hulog Nakaraang Linggo:** P" . number_format($totalPaid, 2) . "\n\n" .
                    "Salamat sa iyong maayos na pagbabayad! 👍";
            } else {
                return "❌ **Walang nakitang hulog noong nakaraang linggo (" . date('M d', strtotime($startOfLastWeek)) . " - " . date('M d, Y', strtotime($endOfLastWeek)) . "):**\n\n" .
                    "Wala kaming nakikitang tala ng iyong bayad para sa nakaraang linggo sa database.";
            }
        }

        // Query 1: Payment Yesterday
        if (strpos($messageText, 'kahapon') !== false) {
            $yesterdayDate = now()->timezone('Asia/Manila')->subDay()->format('Y-m-d');
            $yesterdayPayment = DB::table('clients_payments')
                ->where('client_id', $clientId)
                ->where('due_date', $yesterdayDate)
                ->first();

            $collectorName = $collector ? $collector->fullname : 'iyong Collector';

            if ($yesterdayPayment) {
                return "📅 **Natanggap ang iyong bayad kahapon (" . date('M d, Y', strtotime($yesterdayDate)) . "):**\n\n" .
                    "* **Halaga:** P" . number_format($yesterdayPayment->collection, 2) . "\n" .
                    "* **Payment Type:** " . ucfirst($yesterdayPayment->type ?? 'Daily') . "\n" .
                    "* **Reference No:** " . $yesterdayPayment->reference_number . "\n" .
                    "* **Kinolekta ni:** " . ($collector ? $collector->fullname : 'Collector') . "\n\n" .
                    "Maraming salamat sa iyong maayos na pagbabayad! 👍";
            } else {
                return "❌ **Walang nakitang bayad kahapon (" . date('M d, Y', strtotime($yesterdayDate)) . "):**\n\n" .
                    "Paumanhin, wala kaming nakikitang tala ng iyong bayad kahapon sa aming database.\n\n" .
                    "Mangyaring makipag-ugnayan sa iyong Area Collector na si **" . $collectorName . "** kung ikaw ay nakapag-abot ng bayad upang mai-verify at mai-encode ito.";
            }
        }

        // Query 2: Payment Today
        if (strpos($messageText, 'ngayon') !== false) {
            $todayDate = now()->timezone('Asia/Manila')->format('Y-m-d');
            $todayPayment = DB::table('clients_payments')
                ->where('client_id', $clientId)
                ->where('due_date', $todayDate)
                ->first();

            $collectorName = $collector ? $collector->fullname : 'iyong Collector';

            if ($todayPayment) {
                return "💵 **Natanggap ang iyong bayad ngayong araw (" . date('M d, Y', strtotime($todayDate)) . "):**\n\n" .
                    "* **Halaga:** P" . number_format($todayPayment->collection, 2) . "\n" .
                    "* **Payment Type:** " . ucfirst($todayPayment->type ?? 'Daily') . "\n" .
                    "* **Reference No:** " . $todayPayment->reference_number . "\n" .
                    "* **Kinolekta ni:** " . ($collector ? $collector->fullname : 'Collector') . "\n\n" .
                    "Maraming salamat! Naka-record na ito sa aming database.";
            } else {
                return "⌛ **Walang nakitang bayad ngayong araw (" . date('M d, Y', strtotime($todayDate)) . "):**\n\n" .
                    "Wala pa kaming natatanggap na bayad para sa araw na ito sa system.\n\n" .
                    "Kung kababayad mo lamang sa iyong collector na si **" . $collectorName . "**, maaaring hindi pa ito nai-encode sa portal. Mangyaring mag-antay ng ilang sandali o i-refresh ang iyong page mamaya.";
            }
        }

        // Query 3: Remaining Balance
        if (strpos($messageText, 'balanse') !== false) {
            $loans = DB::table('clients_loans')->where('client_id', $clientId)->get();
            $totalBalance = 0;
            $loanLines = "";

            foreach ($loans as $loan) {
                $balance = $loan->balance;
                
                $statusText = $loan->status;
                if ($balance <= 0) {
                    $statusText = 'paid';
                }
                
                if ($statusText === 'unpaid' || $statusText === 'active' || $statusText === 'lapsed') {
                    $totalBalance += $balance;
                    $loanLines .= "🔹 **Loan Term:** " . date('M d, Y', strtotime($loan->loan_from)) . " hanggang " . date('M d, Y', strtotime($loan->loan_to)) . "\n" .
                                  "   * **Balanse:** P" . number_format($balance, 2) . "\n" .
                                  "   * **Status:** " . ucfirst($statusText) . "\n\n";
                }
            }

            if ($totalBalance > 0) {
                return "📊 **Iyong mga Aktibong Balanse (Outstanding Dues):**\n\n" .
                    $loanLines .
                    "💵 **Kabuong Natitirang Balanse:** P" . number_format($totalBalance, 2) . "\n\n" .
                    "Siguraduhing magbayad sa tamang oras para maiwasan ang lapsed status. Salamat!";
            } else {
                return "🎉 **Balanse Status:**\n\n" .
                    "Sa kasalukuyan, wala kang outstanding o aktibong loan balance! Lahat ng iyong naunang loans ay ganap nang bayad o settled na. Maraming salamat sa iyong magandang record!";
            }
        }

        // Query 4: Total Savings
        if (strpos($messageText, 'naipon') !== false || strpos($messageText, 'savings') !== false) {
            $isFC = $area && stripos($area->location_name ?? '', 'Financial Counselor') !== false;
            if (!$isFC) {
                return "❌ **Savings Status:**\n\nAng programang Savings ay magagamit lamang ng mga kliyente sa ilalim ng **Financial Counselor** area. Ang iyong kasalukuyang area ay hindi sakop ng savings program.";
            }

            $loans = DB::table('clients_loans')->where('client_id', $clientId)->get();
            $totalSavings = 0;

            foreach ($loans as $loan) {
                $statusText = $loan->status;
                $balance = $loan->balance;
                if ($balance <= 0) {
                    $statusText = 'paid';
                }
                
                if ($statusText === 'unpaid' || $statusText === 'active' || $statusText === 'lapsed') {
                    $totalSavings += $loan->savings_balance;
                }
            }

            return "🏦 **Iyong Naipon (Savings):**\n\n" .
                "Ang iyong kabuuang naipon na Savings para sa iyong mga aktibong loan ay **P" . number_format($totalSavings, 2) . "**.\n\n" .
                "Ang savings na ito ay maiipon at maaari mong magamit pagkatapos ng iyong loan term.";
        }

        // Query 5: Assigned Agents
        if (strpos($messageText, 'collector') !== false || strpos($messageText, 'secretary') !== false || strpos($messageText, 'assigned') !== false || strpos($messageText, 'sino') !== false) {
            $collectorName = $collector ? $collector->fullname : 'N/A';
            $collectorPhone = $collector ? ($collector->phone ?? 'N/A') : 'N/A';
            $secretaryName = $secretary ? $secretary->fullname : 'N/A';
            $areaName = $area ? ($area->location_name . " - [" . $area->areas_name . "]") : 'N/A';

            return "👤 **Iyong mga Assigned Staff / Agents:**\n\n" .
                "📍 **Iyong Area:** " . $areaName . "\n\n" .
                "🚚 **Area Collector:** " . $collectorName . " (" . $collectorPhone . ")\n" .
                "💼 **Office Secretary:** " . $secretaryName . "\n\n" .
                "Kung mayroon kang katanungan tungkol sa koleksyon o nais magpa-encode ng bayad, direktang makipag-ugnayan sa iyong Collector.";
        }

        // Default Helpdesk Message
        return "Kamusta! Ako si **Ultra Support**, ang iyong AI Virtual Assistant ng ULC System. 👋\n\n" .
            "Narito ako upang sagutin ang iyong mga tanong tungkol sa iyong account.\n\n" .
            "Mangyaring piliin ang isa sa mga tanong sa ibaba o sumulat ng iyong katanungan.";
    }

    /**
     * Clear all messages in a conversation.
     */
    public function clearMessages(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
        ]);

        $user = Session::get('user');
        $role = Session::get('role');

        if (!$user || !$role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversationId = $request->input('conversation_id');

        // Query conversation and validate ownership
        $convo = DB::table('chat_conversations')->where('id', $conversationId)->first();
        if (!$convo) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // Verify access rights (only client or staff involved in this chat can clear it)
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

        // Delete all messages in the conversation
        DB::table('chat_messages')->where('conversation_id', $conversationId)->delete();

        // Update conversation last_message_at
        DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->update([
                'last_message_at' => null,
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Chat history cleared successfully']);
    }
}
