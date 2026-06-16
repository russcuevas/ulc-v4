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
                    Welcome! <br>Secretary <br> {{ $user->fullname ?? 'User' }}
                </a>
            </div>
        </div>

        <nav class="mt-2 d-flex flex-column">

            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('secretary.dashboard.page') }}"
                        class="nav-link {{ request()->routeIs('secretary.dashboard.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('secretary.collector.page') }}"
                        class="nav-link {{ request()->routeIs('secretary.collector.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p>Collector</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('secretary.areas.page') }}"
                        class="nav-link {{ request()->routeIs('secretary.areas.*', 'secretary.area.*', 'secretary.collections.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-map-marked-alt"></i>
                        <p>Areas</p>
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
