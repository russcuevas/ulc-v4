<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <style>
        .sidebar {
            position: relative;
            height: 100%;
        }

        .logout-bottom {
            position: absolute;
            bottom: 10px;
            width: 100%;
        }

        .nav-sidebar .nav-link.active {
            background-color: #FF5F00 !important;
            color: #fff !important;
        }

        .nav-sidebar .nav-link.active i {
            color: #fff !important;
        }

        .main-header .nav-link {
            color: #ffffff !important;
        }

        .main-header .nav-link i {
            color: #ffffff !important;
        }

        .main-header .navbar-nav .nav-link:hover {
            color: #ffffff !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- TOP BAR --}}
        @include('secretary.components.topbar')
        {{-- END TOP BAR --}}

        {{-- LEFT SIDEBAR --}}
        @include('secretary.components.sidebar')
        {{-- END LEFT SIDEBAR --}}

        {{-- MAIN --}}
        <div class="content-wrapper">



            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0">Manila Area</h1>
                        </div>
                        <div class="col-sm-6 text-sm-right">
                            <p class="m-0"><strong>Showing:</strong>
                                @if (isset($showAllTime) && $showAllTime)
                                    All breakdown
                                @else
                                    From {{ $from ?? now()->startOfMonth()->format('Y-m-d') }} to
                                    {{ $to ?? now()->endOfMonth()->format('Y-m-d') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <form action="{{ route('secretary.dashboard.page') }}" method="GET" class="row g-2">
                        <div class="col-sm-4">
                            <label>From</label>
                            <input type="date" name="from" class="form-control"
                                value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-sm-4">
                            <label>To</label>
                            <input type="date" name="to" class="form-control"
                                value="{{ request('to', now()->endOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @if (isset($areaSummaries) && $areaSummaries->count())
                        @foreach ($areaSummaries as $area)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card card-outline">
                                        <div class="card-header d-flex align-items-center">
                                            <h5 class="mb-0">{{ $area->areas_name }} <small
                                                    class="text-muted">({{ $area->location_name }})</small></h5>
                                            <a href="{{ route('secretary.areas.collections.references', $area->id) }}"
                                                class="btn btn-sm btn-success ml-auto">View Payment References</a>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="small-box bg-light"
                                                        style="border-top: 4px solid #FF5F00;">
                                                        <div class="inner" style="color: #FF5F00;">
                                                            <h3>₱{{ number_format($area->total_loans_amount, 2) }}</h3>
                                                            <p>Total Loans</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="small-box bg-light"
                                                        style="border-top: 4px solid #FF5F00;">
                                                        <div class="inner" style="color: #FF5F00;">
                                                            <h3>₱{{ number_format($area->total_collectibles, 2) }}</h3>
                                                            <p>Total Collectibles</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="small-box bg-light"
                                                        style="border-top: 4px solid #FF5F00;">
                                                        <div class="inner" style="color: #FF5F00;">
                                                            <h3>₱{{ number_format($area->total_collected, 2) }}</h3>
                                                            <p>Total Collections</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">No areas found. Please add area records.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
    <script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('dist/js/demo.js') }}"></script>
    <script src="{{ asset('dist/js/pages/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const notyf = new Notyf({
                duration: 5000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });

            @if (session('success'))
                notyf.success("{{ session('success') }}");
            @endif

            @if (session('error'))
                notyf.error("{{ session('error') }}");
            @endif
        });
    </script>
    <script>
        function updateManilaTime() {
            const options = {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'long',
                day: '2-digit',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };

            const now = new Date();
            const formatted = new Intl.DateTimeFormat('en-US', options).format(now);

            document.getElementById('manila-time').innerText = formatted;
        }

        updateManilaTime();
        setInterval(updateManilaTime, 1000);
    </script>
</body>

</html>
