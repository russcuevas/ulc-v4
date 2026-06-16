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
                            <h1 class="m-0">{{ $location_name }}</h1>
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
                                    <h3 class="card-title">{{ $location_name }}</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                            data-target="#printSalesModal">
                                            <i class="fas fa-print"></i> Print Sales
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="manilaTable" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Area Code</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($areas as $area)
                                                <tr>
                                                    <td>{{ $area->areas_name }}</td>
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
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->areas_name }}</option>
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

            $('#manilaTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "responsive": true
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
