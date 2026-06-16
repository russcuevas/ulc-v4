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

        /* Remove highlight only for submenu items */
        .nav-sidebar .nav-treeview .nav-link.active {
            background-color: transparent !important;
            color: inherit !important;
        }

        /* Orange circle indicator for active submenu */
        .nav-sidebar .nav-treeview .nav-link.active .nav-icon.fa-circle {
            color: #FF5F00 !important;
            font-weight: 900;
            /* makes the circle solid */
        }


        .card-primary.card-outline {
            border-top: 3px solid #FF5F00;
        }

        .lazy-hidden {
            display: none !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- TOP BAR --}}
        @include('secretary.components.topbar')

        {{-- SIDEBAR --}}
        @include('secretary.components.sidebar')

        {{-- MAIN --}}
        <div class="content-wrapper">

            <div class="content-header">
                <div class="container-fluid">
                    <h1 class="m-0">{{ $areas_name }} - Clients</h1>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100">

                                <h3 class="card-title">{{ $areas_name }}</h3>

                                {{-- OPTIONAL: Remove if secretary cannot add --}}
                                <button class="btn btn-success btn-sm px-3" data-toggle="modal"
                                    data-target="#addClientModal">
                                    <i class="fas fa-user-plus"></i> Add Client
                                </button>

                            </div>
                        </div>

                        {{-- OPTIONAL: include if you want add client --}}
                        @include('secretary.areas.modals.add_client')

                        <div class="card-body">
                            <table id="clientsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Phone</th>
                                        <th>Phone 2</th>
                                        <th>Gender</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($clients as $client)
                                        <tr>
                                            <td>{{ $client->fullname }}</td>
                                            <td>{{ $client->phone }}</td>
                                            <td>{{ $client->phone_number_2 }}</td>
                                            <td>{{ $client->gender }}</td>
                                            <td>
                                                <a href="{{ route('secretary.area.clients.loans', $client->id) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View Loans
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

    {{-- SCRIPTS --}}
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        $(function() {
            var table = $('#clientsTable').DataTable({
                paging: false,
                searching: true,
                ordering: true,
                responsive: true
            });

            // Lazy loading variables
            var visibleCount = 30;
            var isLoadPending = false;

            function applyLazyLoading() {
                var matchingRows = $('#clientsTable tbody tr').filter(function() {
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
                    if ($('#clientsTable tbody tr.lazy-hidden').length > 0) {
                        isLoadPending = true;
                        $('#lazyLoader').show();

                        setTimeout(function() {
                            visibleCount += 30;
                            applyLazyLoading();
                            isLoadPending = false;
                        }, 500);
                    }
                }
            });

            // Hook into table draw to reset count and apply lazy loading on search/filter/order changes
            table.on('draw', function() {
                visibleCount = 30;
                applyLazyLoading();
            });

            // Initial call
            applyLazyLoading();
        });
    </script>

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

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    notyf.error("{{ $error }}");
                @endforeach
            @endif

        });
    </script>

</body>

</html>
