<!-- Preloader -->
<div class="preloader flex-column justify-content-center align-items-center">
    <h1 class="animation__shake">ULC</h1>
</div>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand" style="background-color:#FF5F00;">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <style>
            .notification-dropdown-menu .dropdown-item {
                white-space: normal !important;
                display: flex !important;
                align-items: flex-start !important;
                border-bottom: 1px solid #f0f2f5 !important;
            }
            .notification-dropdown-menu .dropdown-item:hover {
                background-color: #f8f9fa !important;
            }
            .notification-dropdown-menu .dropdown-item.unread-item {
                background-color: rgba(255, 95, 0, 0.02) !important;
            }
            .notification-dropdown-menu .dropdown-item.unread-item:hover {
                background-color: rgba(255, 95, 0, 0.05) !important;
            }
            .notification-dropdown-menu .dropdown-footer:hover {
                background-color: #f8f9fa !important;
            }
        </style>
        <li class="nav-item dropdown">
            @php
                use Illuminate\Support\Facades\DB;
                use Illuminate\Support\Facades\Session;

                $sessionUser = Session::get('user');
                $unreadCount = 0;
                $notifications = collect();
                if ($sessionUser) {
                    $role = Session::get('role');
                    $notifiableType = $role === 'collector' ? 'App\Models\Collector' : ($role === 'secretary' ? 'App\Models\Secretary' : 'App\Models\Admin');

                    $areaIds = DB::table('areas')
                        ->where('collector_id', $sessionUser->id)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($areaIds)) {
                        $notificationsQuery = DB::table('area_notifications as an')
                            ->leftJoin('area_notification_reads as r', function ($join) use ($notifiableType, $sessionUser) {
                                $join->on('an.id', '=', 'r.area_notification_id')
                                    ->where('r.notifiable_type', $notifiableType)
                                    ->where('r.notifiable_id', $sessionUser->id);
                            })
                            ->whereIn('an.area_id', $areaIds);

                        $unreadCount = (clone $notificationsQuery)->whereNull('r.read_at')->count();

                        $notifications = $notificationsQuery->select('an.*', 'r.read_at')
                            ->orderBy('an.created_at', 'desc')
                            ->limit(5)
                            ->get();
                    }
                }
            @endphp
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" style="height: 100%;">
                <i class="far fa-bell" style="font-size: 17px;"></i>
                @if($unreadCount > 0)
                    <span class="badge badge-warning navbar-badge" style="background-color: #ffc107 !important; color: #1f2d3d !important; font-weight: bold; border-radius: 10px; padding: 2px 5px; font-size: 9px; top: 4px; right: 4px;">{{ $unreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0 notification-dropdown-menu" style="min-width: 380px !important; max-width: calc(100vw - 30px) !important; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border: 1px solid #eef2f5; overflow: hidden; margin-top: 10px;">
                <div class="dropdown-header px-3 py-3 d-flex justify-content-between align-items-center" style="background: #fff; border-bottom: 1px solid #f0f2f5;">
                    <span class="font-weight-bold text-dark" style="font-size: 15px;">Notifications</span>
                    <span class="badge badge-pill badge-primary" style="background-color: #FF5F00 !important; font-size: 12px; padding: 4px 10px; border-radius: 30px;">{{ $unreadCount }} New</span>
                </div>
                <div style="max-height: 350px; overflow-y: auto;">
                    @if($notifications->isEmpty())
                        <div class="text-center py-5 text-muted bg-white">
                            <i class="far fa-bell fa-2x mb-2" style="color: #cbd5e1;"></i>
                            <p class="mb-0" style="font-size: 13px; font-weight: 500;">No notifications found</p>
                        </div>
                    @else
                        @foreach($notifications as $note)
                            @php
                                $isUnread = is_null($note->read_at);
                                $data = json_decode($note->data, true);
                                $message = $data['message'] ?? 'Notification';
                                
                                // Determine icon and background color based on message content
                                $iconClass = 'fa-info-circle';
                                $iconColor = '#FF5F00';
                                $iconBg = 'rgba(255, 95, 0, 0.1)';
                                if (strpos(strtolower($message), 'payment') !== false) {
                                    $iconClass = 'fa-receipt';
                                    $iconColor = '#28a745';
                                    $iconBg = 'rgba(40, 167, 69, 0.1)';
                                } elseif (strpos(strtolower($message), 'client') !== false) {
                                    $iconClass = 'fa-user-plus';
                                    $iconColor = '#007bff';
                                    $iconBg = 'rgba(0, 123, 255, 0.1)';
                                }
                            @endphp
                            <a href="{{ url('/notifications') }}" class="dropdown-item px-3 py-3 d-flex align-items-start {{ $isUnread ? 'unread-item' : '' }}" style="text-decoration: none;">
                                <div class="mr-3 mt-1 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 50%; background-color: {{ $iconBg }}; color: {{ $iconColor }};">
                                    <i class="fas {{ $iconClass }}" style="font-size: 15px;"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <p class="mb-0 text-dark" style="font-size: 13px; line-height: 1.4; font-weight: {{ $isUnread ? '600' : '400' }}; overflow-wrap: break-word; word-wrap: break-word;">
                                        {{ $message }}
                                    </p>
                                    <span class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="far fa-clock mr-1" style="font-size: 10px;"></i>{{ \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                                @if($isUnread)
                                    <span class="ml-2 mt-2 flex-shrink-0" style="width: 8px; height: 8px; background-color: #FF5F00; border-radius: 50%; display: inline-block;"></span>
                                @endif
                            </a>
                        @endforeach
                    @endif
                </div>
                <a href="{{ url('/notifications') }}" class="dropdown-item dropdown-footer text-center py-3 font-weight-bold" style="color: #FF5F00 !important; background: #fff; border-top: 1px solid #f0f2f5; font-size: 13px; transition: background 0.15s;">
                    See All Notifications <i class="fas fa-chevron-right ml-1" style="font-size: 10px;"></i>
                </a>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
