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
                    <form action="{{ route($rolePrefix . '.dashboard.page') }}" method="GET" class="row g-2">
                        <div class="col-sm-4">
                            <label>From</label>
                            <input type="date" name="from" class="form-control" value="{{ $displayFrom }}">
                        </div>
                        <div class="col-sm-4">
                            <label>To</label>
                            <input type="date" name="to" class="form-control" value="{{ $displayTo }}">
                        </div>
                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Apply Filter</button>
                            <a href="{{ route($rolePrefix . '.dashboard.page') }}"
                                class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
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
                            {{-- Using your existing table-card class for consistent styling --}}
                            <div class="card table-card">
                                <div
                                    class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fas fa-bell mr-2" style="color: #FF5F00;"></i> Notifications
                                    </h5>
                                    @if (!$notifications->isEmpty())
                                        <button id="markAllBtn" class="btn btn-sm btn-primary">
                                            <i class="fas fa-check-double mr-1"></i> Mark All as Read
                                        </button>
                                    @endif
                                </div>

                                <div class="card-body p-0">
                                    @if ($notifications->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted">No notifications found.</p>
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush">
                                            @foreach ($notifications as $note)
                                                @php
                                                    $data = json_decode($note->data, true) ?? [];
                                                    $message = $data['message'] ?? ($data['title'] ?? 'Notification');
                                                    $areaName = $note->area_name ?? ($note->area_id ?? 'N/A');
                                                    $isUnread = is_null($note->read_at);

                                                    // Custom Icons & Colors based on dashboard palette
                                                    $icon = 'fas fa-info-circle';
                                                    $iconColor = '#007bff';
                                                    if (Str::contains($note->type, 'Lapsed')) {
                                                        $icon = 'fas fa-exclamation-triangle';
                                                        $iconColor = '#dc3545'; // Red for urgency
                                                    } elseif (Str::contains($note->type, 'NewClient')) {
                                                        $icon = 'fas fa-user-plus';
                                                        $iconColor = '#28a745'; // Green
                                                    } elseif (Str::contains($note->type, 'Payment')) {
                                                        $icon = 'fas fa-receipt';
                                                        $iconColor = '#FF5F00'; // Brand Orange
                                                    }
                                                @endphp

                                                <div class="list-group-item list-group-item-action border-0 p-3 notification-item {{ $isUnread ? 'unread' : '' }}"
                                                    data-id="{{ $note->id }}"
                                                    style="border-left: 4px solid {{ $isUnread ? '#FF5F00' : 'transparent' }} !important;">

                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                                            style="width: 45px; height: 45px; background: #f8f9fa; border-radius: 50%;">
                                                            <i class="{{ $icon }}"
                                                                style="color: {{ $iconColor }}; font-size: 1.2rem;"></i>
                                                        </div>

                                                        <div class="flex-grow-1">
                                                            <div
                                                                class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <div class="message-text {{ $isUnread ? 'font-weight-bold' : '' }}"
                                                                        style="font-size: 14px; color: #333;">
                                                                        {{ $message }}
                                                                    </div>
                                                                    <div class="small mt-1 text-muted">
                                                                        <span class="badge badge-light shadow-sm">Area:
                                                                            {{ $areaName }}</span>
                                                                        <span class="mx-1">•</span>
                                                                        <i
                                                                            class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}
                                                                    </div>
                                                                </div>

                                                                <div class="action-area text-right ml-2">
                                                                    @if ($isUnread)
                                                                        <button
                                                                            class="btn btn-xs btn-outline-primary mark-read-btn"
                                                                            style="font-size: 11px; border-radius: 20px;">
                                                                            Mark Read
                                                                        </button>
                                                                    @else
                                                                        <i class="fas fa-check-circle text-success"
                                                                            title="Read"></i>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if ($notifications->hasPages())
                                    <div class="card-footer bg-white border-top-0">
                                        <div class="d-flex justify-content-center">
                                            {{ $notifications->links() }}
                                        </div>
                                    </div>
                                @endif
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
                        $item.css('border-left', '4px solid transparent');
                        $btn.replaceWith('<i class="fas fa-check-circle text-success" title="Read"></i>');
                        Notyf && new Notyf().success('Marked read');
                    } else {
                        $btn.prop('disabled', false);
                        Notyf && new Notyf().error('Failed to mark read');
                    }
                })
                .fail(function() {
                    $btn.prop('disabled', false);
                    Notyf && new Notyf().error('Request failed');
                });
        });

        // Mark all notifications on current page as read
        $(document).on('click', '#markAllBtn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            if (!confirm('Mark all notifications on this page as read?')) return;

            $btn.prop('disabled', true);

            $.post("{{ route('notifications.mark.all') }}")
                .done(function(res) {
                    if (res.success) {
                        $('.notification-item.unread').each(function() {
                            const $item = $(this);
                            $item.removeClass('unread');
                            $item.css('border-left', '4px solid transparent');
                            $item.find('.mark-read-btn').replaceWith(
                                '<i class="fas fa-check-circle text-success" title="Read"></i>');
                        });
                        Notyf && new Notyf().success('All marked read');
                    } else {
                        Notyf && new Notyf().error('Failed to mark all');
                        $btn.prop('disabled', false);
                    }
                })
                .fail(function() {
                    Notyf && new Notyf().error('Request failed');
                    $btn.prop('disabled', false);
                });
        });
    </script>
</body>

</html>
