<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
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

        .btn-primary {
            background-color: #FF5F00 !important;
            border-color: #FF5F00 !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #e65500 !important;
            border-color: #e65500 !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 95, 0, 0.25) !important;
        }

        .dashboard-card {
            border-top: 4px solid #FF5F00;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .dashboard-card .label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .dashboard-card .value {
            font-size: 26px;
            font-weight: 700;
            color: #FF5F00;
            line-height: 1.2;
        }

        .dashboard-card .sub {
            font-size: 12px;
            color: #6c757d;
        }

        .chart-card {
            border-top: 4px solid #FF5F00;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .table-card {
            border-top: 4px solid #FF5F00;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #343a40;
        }

        .range-text {
            font-size: 13px;
            color: #6c757d;
        }

        .chart-wrap {
            position: relative;
            height: 320px;
        }

        .chart-wrap.chart-wrap-pie {
            height: 320px;
        }

        .breakdown-table thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #666;
        }

        .breakdown-table td {
            font-size: 13px;
        }

        .breakdown-table tfoot td {
            font-weight: 700;
            background-color: #f8f9fa;
        }

        .outstanding-col {
            color: #dc3545 !important;
            font-weight: 700;
        }

        /* PREMIUM NOTIFICATION UI */
        .notification-item {
            margin-bottom: 12px;
            border-radius: 12px !important;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.015);
            border: 1px solid #eef2f5 !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            background-color: #fbfcfd;
            border-color: #e2e8f0 !important;
        }

        .notification-item.unread {
            background-color: rgba(255, 95, 0, 0.02);
            border-left: 5px solid #FF5F00 !important;
            border-color: rgba(255, 95, 0, 0.12) !important;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 22px;
            right: 20px;
            width: 8px;
            height: 8px;
            background-color: #FF5F00;
            border-radius: 50%;
        }

        .icon-wrapper {
            transition: all 0.3s ease;
            width: 45px;
            height: 45px;
            background: #f8f9fa;
            border-radius: 50%;
        }

        .notification-item:hover .icon-wrapper {
            transform: scale(1.08);
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .mark-read-btn {
            border: 1px solid #FF5F00 !important;
            color: #FF5F00 !important;
            background: transparent !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
            padding: 4px 12px !important;
            border-radius: 30px !important;
            font-size: 11px;
        }

        .mark-read-btn:hover {
            background: #FF5F00 !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(255, 95, 0, 0.2) !important;
        }

        .filter-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f5;
            padding: 20px;
            margin-bottom: 24px;
        }

        .filter-card label {
            font-weight: 600;
            color: #495057;
            font-size: 13px;
        }

        .filter-card .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 12px;
            height: auto;
            transition: all 0.2s ease;
        }

        .filter-card .form-control:focus {
            border-color: #FF5F00;
            box-shadow: 0 0 0 0.2rem rgba(255, 95, 0, 0.15);
        }

        .spinner-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        @php
            $rolePrefix = Session::get('role', 'admin');
        @endphp

        {{-- TOP BAR --}}
        @include($rolePrefix . '.components.topbar')
        {{-- END TOP BAR --}}

        {{-- LEFT SIDEBAR --}}
        @include($rolePrefix . '.components.sidebar')
        {{-- END LEFT SIDEBAR --}}

        {{-- MAIN --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0 section-title">Notifications History</h1>
                            <p class="mb-0 range-text">
                                @if ($isFiltered)
                                    From {{ $displayFrom }} to {{ $displayTo }}
                                @else
                                    All Time Overview
                                @endif
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-right">
                            <h5 class="m-0" id="manila-time"></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="filter-card">
                        <form action="{{ route('notifications.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label><i class="far fa-calendar-alt mr-1"></i> From Date</label>
                                <input type="date" name="from" class="form-control" value="{{ $displayFrom }}">
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label><i class="far fa-calendar-alt mr-1"></i> To Date</label>
                                <input type="date" name="to" class="form-control" value="{{ $displayTo }}">
                            </div>
                            <div class="col-md-4 d-flex">
                                <button type="submit" class="btn btn-primary mr-2 flex-grow-1"
                                    style="background-color: #FF5F00 !important; border-color: #FF5F00 !important; border-radius: 8px; font-weight: 600; padding: 10px 16px;">
                                    <i class="fas fa-filter mr-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('notifications.index') }}"
                                    class="btn btn-outline-secondary flex-grow-1"
                                    style="border-radius: 8px; padding: 10px 16px; font-weight: 600;">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                @php
                    use Illuminate\Support\Str;
                @endphp

                <div class="container-fluid mt-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card table-card" style="border-radius: 16px;">
                                <div
                                    class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                    <h5 class="section-title mb-0 d-inline-block">
                                        <i class="fas fa-bell mr-2" style="color: #FF5F00;"></i> Notifications History
                                    </h5>
                                    @if (!$notifications->isEmpty())
                                        <button id="markAllBtn" class="btn btn-sm btn-primary"
                                            style="border-radius: 8px; font-weight: 600;">
                                            <i class="fas fa-check-double mr-1"></i> Mark All as Read
                                        </button>
                                    @endif
                                </div>

                                <div class="card-body p-4" style="background-color: #f4f6f9;">
                                    @if ($notifications->isEmpty())
                                        <div class="text-center py-5 bg-white rounded-lg shadow-sm border"
                                            style="border-radius: 12px !important;">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted font-weight-bold">No notifications found.</p>
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush" id="notificationContainer"
                                            style="background: transparent;">
                                            @include('notifications.partials.list')
                                        </div>

                                        <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                                            <div class="spinner-border text-primary" role="status"
                                                style="color: #FF5F00 !important; width: 1.5rem; height: 1.5rem;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('dist/js/demo.js') }}"></script>
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
        // Setup AJAX with CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mark single notification as read
        $(document).on('click', '.mark-read-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $item = $btn.closest('.notification-item');
            const id = $item.data('id');

            if (!id) return;

            $btn.prop('disabled', true);

            $.post("{{ route('notifications.mark.read') }}", {
                    id: id
                })
                .done(function(res) {
                    if (res.success) {
                        // update UI to read
                        $item.removeClass('unread');
                        $btn.replaceWith('<i class="fas fa-check-circle text-success" title="Read"></i>');
                        Notyf && new Notyf({
                            duration: 3000,
                            position: {
                                x: 'right',
                                y: 'top'
                            }
                        }).success('Notification marked as read');
                    } else {
                        $btn.prop('disabled', false);
                        Notyf && new Notyf({
                            duration: 3000,
                            position: {
                                x: 'right',
                                y: 'top'
                            }
                        }).error('Failed to mark read');
                    }
                })
                .fail(function() {
                    $btn.prop('disabled', false);
                    Notyf && new Notyf({
                        duration: 3000,
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    }).error('Request failed');
                });
        });

        // Mark all notifications as read
        $(document).on('click', '#markAllBtn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            if (!confirm('Mark all notifications as read?')) return;

            $btn.prop('disabled', true);

            $.post("{{ route('notifications.mark.all') }}")
                .done(function(res) {
                    if (res.success) {
                        $('.notification-item.unread').each(function() {
                            const $item = $(this);
                            $item.removeClass('unread');
                            $item.find('.mark-read-btn').replaceWith(
                                '<i class="fas fa-check-circle text-success" title="Read"></i>');
                        });
                        Notyf && new Notyf({
                            duration: 3000,
                            position: {
                                x: 'right',
                                y: 'top'
                            }
                        }).success('All notifications marked as read');
                    } else {
                        Notyf && new Notyf({
                            duration: 3000,
                            position: {
                                x: 'right',
                                y: 'top'
                            }
                        }).error('Failed to mark all as read');
                        $btn.prop('disabled', false);
                    }
                })
                .fail(function() {
                    Notyf && new Notyf({
                        duration: 3000,
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    }).error('Request failed');
                    $btn.prop('disabled', false);
                });
        });

        // INFINITE SCROLL
        var page = 1;
        var hasMorePages = {{ $notifications->hasMorePages() ? 'true' : 'false' }};
        var loading = false;

        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 150) {
                if (hasMorePages && !loading) {
                    loadMoreNotifications();
                }
            }
        });

        function loadMoreNotifications() {
            loading = true;
            $('#loadingIndicator').show();
            page++;

            var url = new URL(window.location.href);
            url.searchParams.set('page', page);

            $.ajax({
                url: url.toString(),
                type: 'GET',
                success: function(data) {
                    if (data.trim() === '') {
                        hasMorePages = false;
                        $('#loadingIndicator').html(
                            '<p class="text-muted small py-2 font-weight-bold">No more notifications.</p>');
                    } else {
                        $('#notificationContainer').append(data);
                        loading = false;
                        $('#loadingIndicator').hide();
                    }
                },
                error: function() {
                    loading = false;
                    $('#loadingIndicator').hide();
                    Notyf && new Notyf({
                        duration: 3000,
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    }).error('Failed to load more notifications.');
                }
            });
        }
    </script>
</body>

</html>
