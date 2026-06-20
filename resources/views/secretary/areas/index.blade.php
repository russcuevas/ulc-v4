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
                            <h1 class="m-0" id="pageHeader">Assigned Areas</h1>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">

                        <!-- Collector Table -->
                        <div class="col-md-12">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title" id="cardHeader">All Assigned Areas</h3>
                                    <div class="card-tools">
                                        <div class="d-flex align-items-center">
                                            <a id="weeklyCollectionBtn" href="{{ $areas->first() ? route('secretary.areas.collections.weekly', ['location' => $areas->first()->location_name]) : '#' }}"
                                                class="btn btn-success btn-sm mr-2{{ $areas->first() ? '' : ' disabled' }}">
                                                <i class="fas fa-hand-holding"></i> Weekly Collection
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                data-target="#printSalesModal">
                                                <i class="fas fa-print"></i> Print Sales
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label for="locationFilter" class="font-weight-bold"><i
                                                        class="fas fa-filter"></i> Filter by Location</label>
                                                <select id="locationFilter" class="form-control">
                                                    <option value="all">-- All Locations --</option>
                                                    @foreach ($areas->pluck('location_name')->unique() as $location)
                                                        <option value="{{ $location }}">{{ $location }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <table id="manilaTable" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>Area Code</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($areas->groupBy(fn($a) => $a->location_name . '|' . $a->areas_name) as $key => $rows)
                                                @php
                                                    $area = $rows->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $area->location_name }}</td>
                                                    <td
                                                        data-order="{{ preg_replace_callback('/\d+/', fn($m) => sprintf('%04d', $m[0]), $area->areas_name) }}">
                                                        {{ $area->areas_name }}</td>
                                                    <td>
                                                        <a href="{{ route('secretary.areas.clients.page', $area->id) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> View Clients
                                                        </a>
                                                        <a href="{{ route('secretary.areas.collections.references', $area->id) }}"
                                                            class="btn btn-sm btn-success">
                                                            <i class="fas fa-hand-holding-usd"></i> Collections
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
    </div>

    <!-- Print Sales Modal -->
    <div class="modal fade" id="printSalesModal" tabindex="-1" role="dialog" aria-labelledby="printSalesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('secretary.areas.sales.report.print') }}" method="POST" target="_blank">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="printSalesModalLabel">
                            <i class="fas fa-print"></i> Print Sales Reports
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> FROM DATE</label>
                            <input type="date" name="from" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> TO DATE</label>
                            <input type="date" name="to" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> AREA</label>
                            <select name="area_id" class="form-control" id="areaSelect" required>
                                <option value="">-- Select Area --</option>
                                @foreach ($areas->groupBy(fn($a) => $a->location_name . '|' . $a->areas_name) as $key => $rows)
                                    @php
                                        $area = $rows->first();
                                    @endphp
                                    <option value="{{ $area->id }}">{{ $area->location_name }} -
                                        {{ $area->areas_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="all_areas" value="1"
                                id="allAreasCheck">
                            <label class="form-check-label" for="allAreasCheck">
                                [CLICK THIS BOX IF ALL AREAS]
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
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

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    notyf.error("{{ $error }}");
                @endforeach
            @endif

        });
    </script>
    <script>
        $(function() {

            function setupLazyLoad(tableId, initialCount = 15) {
                var table = $(tableId).DataTable({
                    "paging": false,
                    "searching": true,
                    "ordering": true,
                    "responsive": true
                });

                var visibleCount = initialCount;
                var isLoadPending = false;
                var loaderId = tableId.replace('#', '') + '_lazyLoader';

                var loaderHtml = '<div id="' + loaderId +
                    '" class="text-center py-3 lazy-loader-indicator" style="display: none;">' +
                    '<div class="spinner-border text-primary" role="status" style="color: #FF5F00 !important;">' +
                    '<span class="sr-only">Loading...</span>' +
                    '</div>' +
                    '</div>';
                $(tableId).after(loaderHtml);

                if ($('#lazy-hidden-style').length === 0) {
                    $('head').append(
                        '<style id="lazy-hidden-style">.lazy-hidden { display: none !important; }</style>');
                }

                function applyLazyLoading() {
                    var matchingRows = $(tableId + ' tbody tr').filter(function() {
                        return $(this).css('display') !== 'none' || $(this).hasClass('lazy-hidden');
                    });

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
                        $('#' + loaderId).show();
                    } else {
                        $('#' + loaderId).hide();
                    }
                }

                $(window).on('scroll', function() {
                    if (isLoadPending) return;

                    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                        if ($(tableId + ' tbody tr.lazy-hidden').length > 0) {
                            isLoadPending = true;
                            $('#' + loaderId).show();

                            setTimeout(function() {
                                visibleCount += 15;
                                applyLazyLoading();
                                isLoadPending = false;
                            }, 500);
                        }
                    }
                });

                table.on('draw', function() {
                    visibleCount = initialCount;
                    applyLazyLoading();
                });

                applyLazyLoading();
                return table;
            }

            var table = setupLazyLoad('#manilaTable');

            // Custom search filter for exact match on location column (index 0)
            $('#locationFilter').on('change', function() {
                var val = $(this).val();

                // Update page title and card title
                var displayTitle = (val === 'all') ? 'Assigned Areas' : val;
                var displayCard = (val === 'all') ? 'All Assigned Areas' : val;
                $('#pageHeader').text(displayTitle);
                $('#cardHeader').text(displayCard);

                // Update Weekly Collection button link
                var weeklyUrl = "";
                if (val === 'all') {
                    var firstLoc = "{{ $areas->first()->location_name ?? '' }}";
                    if (firstLoc) {
                        weeklyUrl = "{{ route('secretary.areas.collections.weekly', ['location' => ':location']) }}".replace(':location', encodeURIComponent(firstLoc));
                        $('#weeklyCollectionBtn').removeClass('disabled').attr('href', weeklyUrl);
                    } else {
                        $('#weeklyCollectionBtn').addClass('disabled').attr('href', '#');
                    }
                } else {
                    weeklyUrl = "{{ route('secretary.areas.collections.weekly', ['location' => ':location']) }}".replace(':location', encodeURIComponent(val));
                    $('#weeklyCollectionBtn').removeClass('disabled').attr('href', weeklyUrl);
                }

                if (val === 'all') {
                    table.column(0).search('').draw();
                } else {
                    // Exact regex match
                    var escapedVal = $.fn.dataTable.util.escapeRegex(val);
                    table.column(0).search('^' + escapedVal + '$', true, false).draw();
                }
            });

            $('#allAreasCheck').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#areaSelect').prop('required', false).prop('disabled', true);
                } else {
                    $('#areaSelect').prop('required', true).prop('disabled', false);
                }
            });
            // Pre-select area when opened from a row button
            $(document).on('click', '.btn-print-sales', function() {
                var areaId = $(this).data('area-id');
                $('#areaSelect').val(areaId).prop('disabled', false).prop('required', true);
                $('#allAreasCheck').prop('checked', false);
            });

            // Reset modal when opened from top card-tools button (no area pre-selected)
            $('#printSalesModal').on('show.bs.modal', function(e) {
                if (!$(e.relatedTarget).hasClass('btn-print-sales')) {
                    $('#areaSelect').val('').prop('disabled', false).prop('required', true);
                    $('#allAreasCheck').prop('checked', false);
                }
            });
        });
    </script>
</body>

</html>
