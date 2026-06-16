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

        {{-- TOP BAR --}}
        @include('admin.components.topbar')
        {{-- END TOP BAR --}}

        {{-- LEFT SIDEBAR --}}
        @include('admin.components.sidebar')
        {{-- END LEFT SIDEBAR --}}

        {{-- MAIN --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0 section-title">Admin Analytics Dashboard</h1>
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
                    <form action="{{ route('admin.dashboard.page') }}" method="GET" class="row g-2">
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
                            <a href="{{ route('admin.dashboard.page') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">


                    <div class="row mb-2">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#breakdownDetailsModal">
                                <i class="fas fa-list-ul mr-1"></i> View Breakdown Details
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="dashboard-card p-3">
                                <div class="label">Total Loan Amount</div>
                                <div class="value">P{{ number_format($overall['total_loans_amount'], 2) }}</div>
                                <div class="sub">{{ number_format($overall['total_loans']) }} loans in range</div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="dashboard-card p-3">
                                <div class="label">Total Collected</div>
                                <div class="value">P{{ number_format($overall['total_collected'], 2) }}</div>
                                <div class="sub">From daily collections</div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="dashboard-card p-3">
                                <div class="label">Collectibles</div>
                                <div class="value">P{{ number_format($overall['total_collectibles'], 2) }}</div>
                                <div class="sub">Expected for selected range</div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="dashboard-card p-3">
                                <div class="label">Coverage</div>
                                <div class="value">{{ $overall['locations'] }} / {{ $overall['areas'] }}</div>
                                <div class="sub">Locations / Areas monitored</div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <div class="chart-card p-3">
                                <h5 class="mb-3">Loans vs Collections by Location</h5>
                                <div class="chart-wrap">
                                    <canvas id="locationBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="chart-card p-3">
                                <h5 class="mb-3">Loan Status Distribution</h5>
                                <div class="chart-wrap chart-wrap-pie">
                                    <canvas id="loanStatusPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <div class="chart-card p-3">
                                <h5 class="mb-3">Collections by Area</h5>
                                <div class="chart-wrap">
                                    <canvas id="areaBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="chart-card p-3">
                                <h5 class="mb-3">Payment Type Share</h5>
                                <div class="chart-wrap chart-wrap-pie">
                                    <canvas id="paymentTypePieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="breakdownDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="breakdownDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="breakdownDetailsModalLabel">
                        <i class="fas fa-chart-pie mr-1"></i> Analytics Breakdown Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-2">By Location</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover breakdown-table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Total Clients</th>
                                    <th>Total Loans</th>
                                    <th>New</th>
                                    <th>Renewal</th>
                                    <th>Loan Amount</th>
                                    <th>Collectibles</th>
                                    <th>Collected</th>
                                    <th>Outstanding</th>
                                    <th>Lapsed Amount</th>
                                    <th class="outstanding-col">Total Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locationSummaries as $location)
                                    <tr>
                                        <td>{{ $location->location_name }}</td>
                                        <td>{{ number_format($location->total_clients) }}</td>
                                        <td>{{ number_format($location->total_loans) }}</td>
                                        <td>{{ number_format($location->new_loan_count ?? 0) }}</td>
                                        <td>{{ number_format($location->renewal_loan_count ?? 0) }}</td>
                                        <td>P{{ number_format($location->total_loans_amount, 2) }}</td>
                                        <td>P{{ number_format($location->total_collectibles, 2) }}</td>
                                        <td>P{{ number_format($location->total_collected, 2) }}</td>
                                        <td>P{{ number_format($location->total_active_balance, 2) }}</td>
                                        <td>P{{ number_format($location->total_lapsed_balance, 2) }}</td>
                                        <td class="outstanding-col">P{{ number_format($location->total_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No location breakdown available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>TOTAL</td>
                                    <td>{{ number_format($locationSummaries->sum('total_clients')) }}</td>
                                    <td>{{ number_format($locationSummaries->sum('total_loans')) }}</td>
                                    <td>{{ number_format($locationSummaries->sum('new_loan_count')) }}</td>
                                    <td>{{ number_format($locationSummaries->sum('renewal_loan_count')) }}</td>
                                    <td>P{{ number_format($locationSummaries->sum('total_loans_amount'), 2) }}</td>
                                    <td>P{{ number_format($locationSummaries->sum('total_collectibles'), 2) }}</td>
                                    <td>P{{ number_format($locationSummaries->sum('total_collected'), 2) }}</td>
                                    <td>P{{ number_format($locationSummaries->sum('total_active_balance'), 2) }}</td>
                                    <td>P{{ number_format($locationSummaries->sum('total_lapsed_balance'), 2) }}</td>
                                    <td class="outstanding-col">
                                        P{{ number_format($locationSummaries->sum('total_balance'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <h6 class="mb-2">By Area</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover breakdown-table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Area</th>
                                    <th>Total Clients</th>
                                    <th>Total Loans</th>
                                    <th>New</th>
                                    <th>Renewal</th>
                                    <th>Loan Amount</th>
                                    <th>Collectibles</th>
                                    <th>Collected</th>
                                    <th>Outstanding</th>
                                    <th>Lapsed Amount</th>
                                    <th class="outstanding-col">Total Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($areaSummaries as $area)
                                    <tr>
                                        <td>{{ $area->location_name }}</td>
                                        <td>{{ $area->areas_name }}</td>
                                        <td>{{ number_format($area->total_clients) }}</td>
                                        <td>{{ number_format($area->total_loans) }}</td>
                                        <td>{{ number_format($area->new_loan_count ?? 0) }}</td>
                                        <td>{{ number_format($area->renewal_loan_count ?? 0) }}</td>
                                        <td>P{{ number_format($area->total_loans_amount, 2) }}</td>
                                        <td>P{{ number_format($area->total_collectibles, 2) }}</td>
                                        <td>P{{ number_format($area->total_collected, 2) }}</td>
                                        <td>P{{ number_format($area->total_active_balance, 2) }}</td>
                                        <td>P{{ number_format($area->total_lapsed_balance, 2) }}</td>
                                        <td class="outstanding-col">P{{ number_format($area->total_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">No area breakdown available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">TOTAL</td>
                                    <td>{{ number_format($areaSummaries->sum('total_clients')) }}</td>
                                    <td>{{ number_format($areaSummaries->sum('total_loans')) }}</td>
                                    <td>{{ number_format($areaSummaries->sum('new_loan_count')) }}</td>
                                    <td>{{ number_format($areaSummaries->sum('renewal_loan_count')) }}</td>
                                    <td>P{{ number_format($areaSummaries->sum('total_loans_amount'), 2) }}</td>
                                    <td>P{{ number_format($areaSummaries->sum('total_collectibles'), 2) }}</td>
                                    <td>P{{ number_format($areaSummaries->sum('total_collected'), 2) }}</td>
                                    <td>P{{ number_format($areaSummaries->sum('total_active_balance'), 2) }}</td>
                                    <td>P{{ number_format($areaSummaries->sum('total_lapsed_balance'), 2) }}</td>
                                    <td class="outstanding-col">
                                        P{{ number_format($areaSummaries->sum('total_balance'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
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
        const chartData = {
            locationLabels: @json($charts['locationLabels']),
            locationLoans: @json($charts['locationLoans']),
            locationCollected: @json($charts['locationCollected']),
            areaLabels: @json($charts['areaLabels']),
            areaCollections: @json($charts['areaCollections']),
            areaLoans: @json($charts['areaLoans']),
            loanStatusLabels: @json($charts['loanStatusLabels']),
            loanStatusValues: @json($charts['loanStatusValues']),
            paymentTypeLabels: @json($charts['paymentTypeLabels']),
            paymentTypeValues: @json($charts['paymentTypeValues'])
        };

        function buildCharts() {
            new Chart(document.getElementById('locationBarChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartData.locationLabels,
                    datasets: [{
                            label: 'Loan Amount',
                            backgroundColor: 'rgba(255, 95, 0, 0.75)',
                            borderColor: 'rgba(255, 95, 0, 1)',
                            borderWidth: 1,
                            data: chartData.locationLoans
                        },
                        {
                            label: 'Collected',
                            backgroundColor: 'rgba(40, 167, 69, 0.75)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1,
                            data: chartData.locationCollected
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false
                }
            });

            new Chart(document.getElementById('areaBarChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartData.areaLabels,
                    datasets: [{
                            label: 'Collections',
                            backgroundColor: 'rgba(0, 123, 255, 0.7)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                            data: chartData.areaCollections
                        },
                        {
                            label: 'Loan Amount',
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1,
                            data: chartData.areaLoans
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false
                }
            });

            new Chart(document.getElementById('loanStatusPieChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: chartData.loanStatusLabels,
                    datasets: [{
                        data: chartData.loanStatusValues,
                        backgroundColor: ['#FF5F00', '#28a745', '#007bff', '#ffc107', '#6c757d', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false
                }
            });

            new Chart(document.getElementById('paymentTypePieChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: chartData.paymentTypeLabels,
                    datasets: [{
                        data: chartData.paymentTypeValues,
                        backgroundColor: ['#17a2b8', '#FF5F00', '#28a745', '#dc3545', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false
                }
            });
        }

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

        $(function() {
            buildCharts();
        });
    </script>
</body>

</html>
