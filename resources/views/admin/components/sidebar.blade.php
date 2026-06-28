<aside class="main-sidebar sidebar-light-primary elevation-4" style="background-color:#f8f9fa;">
    <a href="index3.html" class="brand-link" style="background-color: #FF5F00; color: white;">
        <i class="fas fa-database img-circle elevation-3 ml-3"></i>
        <span class="brand-text font-weight-light">ULC System</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                @php $user = Session::get('user'); @endphp

                <a href="#" class="d-block" style="color: #FF5F00;">
                    Welcome! <br> Administrator <br> {{ $user->fullname ?? 'User' }}
                </a>
            </div>
        </div>

        <nav class="mt-2 d-flex flex-column">

            @php
                $allAreas = \App\Models\Areas::query()
                    ->select('location_name', 'areas_name')
                    ->groupBy('location_name', 'areas_name')
                    ->orderBy('location_name')
                    ->orderBy('areas_name')
                    ->get();
                $areasByLocation = $allAreas->groupBy('location_name');
                $isAreasActive = request()->routeIs('admin.areas.*', 'admin.area.*');
                $currentLocation = request()->route('location');
                $routeName = request()->route()?->getName();

                if (!$currentLocation) {
                    if (request()->routeIs('admin.areas.clients.page')) {
                        $areaId = request()->route('id');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (request()->routeIs('admin.area.clients.add')) {
                        $areaId = request()->route('id');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (
                        request()->routeIs(
                            'admin.area.clients.loans',
                            'admin.area.clients.update',
                            'admin.area.clients.renew.loan.add',
                            'admin.area.clients.print_summary_loan',
                        )
                    ) {
                        $clientId = request()->route('id');
                        $areaId = \Illuminate\Support\Facades\DB::table('clients')
                            ->where('id', $clientId)
                            ->value('area_id');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (request()->routeIs('admin.area.clients.generate.soa')) {
                        $loanId = request()->route('loanId');
                        $clientId = \Illuminate\Support\Facades\DB::table('clients_loans')
                            ->where('id', $loanId)
                            ->value('client_id');
                        $areaId = \Illuminate\Support\Facades\DB::table('clients')
                            ->where('id', $clientId)
                            ->value('area_id');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (
                        request()->routeIs(
                            'admin.areas.collections.references',
                            'admin.areas.collections.summary.print',
                        )
                    ) {
                        $areaId = request()->route('areaId');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (request()->routeIs('admin.collections.detail')) {
                        $referenceNumber = request()->route('referenceNumber');
                        $areaId = \Illuminate\Support\Facades\DB::table('clients_payments')
                            ->where('reference_number', $referenceNumber)
                            ->value('client_area');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    } elseif (request()->routeIs('admin.collections.print', 'admin.collections.collect')) {
                        $refNo = request()->route('refNo');
                        $areaId = \Illuminate\Support\Facades\DB::table('clients_payments')
                            ->where('reference_number', $refNo)
                            ->value('client_area');
                        $currentLocation = \App\Models\Areas::where('id', $areaId)->value('location_name');
                    }
                }
            @endphp

            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('admin.dashboard.page') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.secretary.page') }}"
                        class="nav-link {{ request()->routeIs('admin.secretary.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>Secretary</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.collector.page') }}"
                        class="nav-link {{ request()->routeIs('admin.collector.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p>Collector</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.chat.page') }}"
                        class="nav-link {{ request()->routeIs('admin.chat.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>Chat Support</p>
                    </a>
                </li>

                <li class="nav-item {{ $isAreasActive ? 'menu-open' : '' }}">
                    <a href="{{ route('admin.areas.page') }}" class="nav-link {{ $isAreasActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-map-marked-alt"></i>
                        <p>
                            Areas
                            <i class="fas fa-angle-left right"></i>
                            <span class="badge badge-info right" style="background-color: #FF5F00;">
                                {{ $allAreas->count() }} areas
                            </span>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        @forelse ($areasByLocation as $location => $areas)
                            <li class="nav-item">
                                <a href="{{ route('admin.areas.location.page', ['location' => $location]) }}"
                                    class="nav-link {{ $currentLocation === $location ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p style="font-size: 12px;">{{ $location }} ({{ $areas->count() }})</p>
                                </a>
                            </li>
                        @empty
                            <li class="nav-item px-3 py-2 text-muted small">No areas found</li>
                        @endforelse
                    </ul>

                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-toggle="modal" data-target="#changePasswordModal">
                        <i class="nav-icon fas fa-key"></i>
                        <p>Change Password</p>
                    </a>
                </li>

            </ul>

            <!-- Logout -->
            <ul class="nav nav-pills nav-sidebar logout-bottom">
                <li class="nav-item">
                    <form action="{{ route('auth.logout.request') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-danger"
                            style="border:none; background:none; width:100%; text-align:left;">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </button>
                    </form>
                </li>
            </ul>

        </nav>

    </div>
    <!-- /.sidebar -->
</aside>

@include('admin.components.change_password_modal')
