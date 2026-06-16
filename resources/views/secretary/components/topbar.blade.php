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
        <li class="nav-item dropdown">
            @php
                use Illuminate\Support\Facades\DB;
                use Illuminate\Support\Facades\Session;

                $sessionUser = Session::get('user');
                $unreadCount = 0;
                $notifications = collect();
                if ($sessionUser) {
                    $notifiableType = get_class($sessionUser);

                    $areaIds = DB::table('areas')
                        ->where('secretary_id', $sessionUser->id)
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
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $unreadCount }} Notifications</span>
                <div class="dropdown-divider"></div>
                @foreach($notifications as $note)
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2"></i> {{ json_decode($note->data, true)['message'] ?? 'Notification' }}
                        <span class="float-right text-muted text-sm">{{ \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                @endforeach
                <a href="{{ url('/notifications') }}" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
    </ul>
</nav>
