<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $sessionUser = Session::get('user');
        if (!$sessionUser) {
            return redirect('/');
        }

        $role = Session::get('role');
        $notifiableType = $role === 'collector' ? 'App\Models\Collector' : ($role === 'secretary' ? 'App\Models\Secretary' : 'App\Models\Admin');

        // Determine accessible area IDs based on user role
        if ($role === 'collector') {
            $areaIds = DB::table('areas')->where('collector_id', $sessionUser->id)->pluck('id')->toArray();
        } elseif ($role === 'secretary') {
            $areaIds = DB::table('areas')->where('secretary_id', $sessionUser->id)->pluck('id')->toArray();
        } else {
            // Admin or other roles see all areas
            $areaIds = DB::table('areas')->pluck('id')->toArray();
        }

        if (empty($areaIds)) {
            $notifications = collect();
        } else {
            $notifications = DB::table('area_notifications as an')
                ->leftJoin('area_notification_reads as r', function ($join) use ($notifiableType, $sessionUser) {
                    $join->on('an.id', '=', 'r.area_notification_id')
                        ->where('r.notifiable_type', $notifiableType)
                        ->where('r.notifiable_id', $sessionUser->id);
                })
                ->leftJoin('areas as a', 'an.area_id', '=', 'a.id')
                ->whereIn('an.area_id', $areaIds)
                ->select('an.*', 'r.read_at', 'a.areas_name as area_name', 'a.location_name')
                ->orderBy('an.created_at', 'desc')
                ->paginate(50);
        }

        return view('notifications.index', [
            'notifications' => $notifications,
            // Defaults for dashboard header used in the view
            'isFiltered' => false,
            'displayFrom' => '',
            'displayTo' => '',
        ]);
    }

    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $sessionUser = Session::get('user');
        if (!$sessionUser || !$id) {
            return response()->json(['success' => false], 400);
        }

        $role = Session::get('role');
        $notifiableType = $role === 'collector' ? 'App\Models\Collector' : ($role === 'secretary' ? 'App\Models\Secretary' : 'App\Models\Admin');

        DB::table('area_notification_reads')->updateOrInsert(
            [
                'area_notification_id' => $id,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $sessionUser->id
            ],
            [
                'read_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $sessionUser = Session::get('user');
        if (!$sessionUser) {
            return response()->json(['success' => false], 400);
        }

        $role = Session::get('role');
        $notifiableType = $role === 'collector' ? 'App\Models\Collector' : ($role === 'secretary' ? 'App\Models\Secretary' : 'App\Models\Admin');

        if ($role === 'collector') {
            $areaIds = DB::table('areas')->where('collector_id', $sessionUser->id)->pluck('id')->toArray();
        } elseif ($role === 'secretary') {
            $areaIds = DB::table('areas')->where('secretary_id', $sessionUser->id)->pluck('id')->toArray();
        } else {
            $areaIds = DB::table('areas')->pluck('id')->toArray();
        }

        if (!empty($areaIds)) {
            $notifications = DB::table('area_notifications')->whereIn('area_id', $areaIds)->pluck('id');

            foreach ($notifications as $nid) {
                DB::table('area_notification_reads')->updateOrInsert(
                    [
                        'area_notification_id' => $nid,
                        'notifiable_type' => $notifiableType,
                        'notifiable_id' => $sessionUser->id
                    ],
                    [
                        'read_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );
            }
        }

        return response()->json(['success' => true]);
    }
}
