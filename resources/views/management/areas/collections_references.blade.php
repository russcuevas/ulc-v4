<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
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

        /* Remove highlight only for submenu items */
        .nav-sidebar .nav-treeview .nav-link.active {
            background-color: transparent !important;
            color: inherit !important;
        }

        /* Orange circle indicator for active submenu */
        .nav-sidebar .nav-treeview .nav-link.active .nav-icon.fa-circle {
            color: #FF5F00 !important;
            font-weight: 900;
        }

        .card-primary.card-outline {
            border-top: 3px solid #FF5F00;
        }

        /* Breadcrumb Link Color (Not Active) */
        .breadcrumb-item a {
            color: #FF5F00 !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        /* Breadcrumb Hover State */
        .breadcrumb-item a:hover {
            color: #cc4c00 !important;
        }

        /* Breadcrumb Separator (The "/" icon) */
        .breadcrumb-item+.breadcrumb-item::before {
            color: #ffa366;
        }

        /* Breadcrumb Active State (The current page) */
        .breadcrumb-item.active {
            color: #6c757d;
        }

        .action-buttons button {
            margin-right: 10px;
        }

        .action-buttons button:last-child {
            margin-right: 0;
        }

        .lazy-hidden {
            display: none !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- TOP BAR --}}
        @include('management.components.topbar')
        {{-- END TOP BAR --}}

        {{-- LEFT SIDEBAR --}}
        @include('management.components.sidebar')
        {{-- END LEFT SIDEBAR --}}

        {{-- MAIN --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <!-- Page Title -->
                            <h1 class="m-0">{{ $location_name }} - [{{ $areas_name }}]</h1>

                            <!-- Breadcrumb -->
                            <ol class="breadcrumb mt-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('management.dashboard.page') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ route('management.areas.location.page', ['location' => $location_name]) }}">Areas</a>
                                </li>
                                <li class="breadcrumb-item active">{{ $areas_name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Payment References - {{ $areas_name }}</h3>
                        </div>
                        <div class="card-body">
                            <table id="referencesTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference Number</th>
                                        <th>Date</th>
                                        <th>Collected By</th>
                                        <th>Total Clients</th>
                                        <th>Total Collectibles</th>
                                        <th>Total Collections</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($references as $ref)
                                        <tr>
                                            <td>{{ $ref->reference_number }}</td>
                                            <td data-order="{{ $ref->due_date }}">
                                                {{ \Carbon\Carbon::parse($ref->due_date)->format('F j, Y') }}</td>
                                            <td>{{ $ref->collected_by_name ?? 'N/A' }}</td>
                                            <td>{{ $ref->total_clients }}</td>
                                            <td>₱{{ number_format($ref->total_daily_collectibles ?? 0, 2) }}</td>
                                            <td>₱{{ number_format($ref->total_collections ?? 0, 2) }}</td>
                                            <td>
                                                <a href="{{ route('management.collections.detail', $ref->reference_number) }}"
                                                    class="btn btn-sm btn-info"
                                                    style="background-color: #FF5F00; border-color: #FF5F00;">
                                                    <i class="fas fa-eye mr-1"></i> View Transactions
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div id="lazyLoader" class="text-center py-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status"
                                    style="color: #FF5F00 !important;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('dist/js/demo.js') }}"></script>
    <script>
        $(function() {

            var table = $('#referencesTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "order": [
                    [1, "desc"]
                ]
            });

            // Lazy loading variables
            var visibleCount = 15;
            var isLoadPending = false;

            function applyLazyLoading() {
                var matchingRows = $('#referencesTable tbody tr').filter(function() {
                    return $(this).css('display') !== 'none' || $(this).hasClass('lazy-hidden');
                });

                var totalMatching = matchingRows.length;
                var hasMore = false;

                matchingRows.each(function(index) {
                    if (index >= visibleCount) {
                        $(this).addClass('lazy-hidden').css('display', 'none');
                        hasMore = true;
                    } else {
                        $(this).removeClass('lazy-hidden');
                        if ($(this).css('display') === 'none') {
                            $(this).css('display', '');
                        }
                    }
                });

                if (hasMore) {
                    $('#lazyLoader').show();
                } else {
                    $('#lazyLoader').hide();
                }
            }

            // On scroll, check if we need to load more
            $(window).on('scroll', function() {
                if (isLoadPending) return;

                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                    if ($('#referencesTable tbody tr.lazy-hidden').length > 0) {
                        isLoadPending = true;
                        $('#lazyLoader').show();

                        setTimeout(function() {
                            visibleCount += 15;
                            applyLazyLoading();
                            isLoadPending = false;
                        }, 500);
                    }
                }
            });

            // Hook into table draw to reset count and apply lazy loading on search/filter/order changes
            table.on('draw', function() {
                visibleCount = 15;
                applyLazyLoading();
            });

            // Initial call
            applyLazyLoading();

        });
    </script>
</body>

</html>
