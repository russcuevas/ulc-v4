<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System - Collection Report</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

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

        .report-card {
            border-top: 4px solid #FF5F00;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .location-row {
            padding: 15px 20px;
            border-bottom: 1px solid #f2f2f2;
            transition: background-color 0.2s;
        }

        .location-row:hover {
            background-color: #fcfcfc;
        }

        .location-name {
            font-size: 16px;
            font-weight: 600;
            color: #343a40;
        }

        .collection-amount {
            font-size: 18px;
            font-weight: 700;
            color: #FF5F00;
        }

        .total-box {
            background-color: #FFF5F0;
            border: 1px solid #FFE0D1;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .total-label {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
        }

        .total-value {
            font-size: 28px;
            font-weight: 700;
            color: #FF5F00;
        }

        .datepicker-container {
            max-width: 300px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                padding-top: 0 !important;
            }
            .report-card {
                box-shadow: none !important;
                border: none !important;
            }
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
                            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 24px;">
                                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Collection Report
                            </h1>
                        </div>
                        <div class="col-sm-6 text-sm-right no-print">
                            <button onclick="window.print()" class="btn btn-outline-secondary font-weight-bold">
                                <i class="fas fa-print mr-1"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">

                            <!-- Date selector Card -->
                            <div class="card report-card mb-4 no-print">
                                <div class="card-body">
                                    <form action="{{ route('management.collection.report.page') }}" method="GET" id="dateFilterForm">
                                        <div class="form-group mb-0 datepicker-container">
                                            <label for="date" class="font-weight-bold text-secondary mb-2">Select Date</label>
                                            <div class="input-group">
                                                <input type="date" name="date" id="date" class="form-control form-control-lg" 
                                                    value="{{ $selectedDate }}" onchange="document.getElementById('dateFilterForm').submit();">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt text-primary"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Report Details Card -->
                            <div class="card report-card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                                    <h3 class="card-title font-weight-bold text-dark m-0">
                                        <i class="fas fa-map-marked-alt text-primary mr-2"></i>
                                        Collections for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                                    </h3>
                                </div>
                                <div class="card-body p-0">
                                    @if($report->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fas fa-folder-open text-muted mb-3" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-0">No locations found in the system.</p>
                                        </div>
                                    @else
                                        @foreach($report as $item)
                                            <div class="d-flex justify-content-between align-items-center location-row">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-city mr-3 text-secondary" style="font-size: 20px;"></i>
                                                    <span class="location-name">{{ $item->location_name }}</span>
                                                </div>
                                                <div class="collection-amount">
                                                    ₱{{ number_format($item->total_collection, 2) }}
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Summary Box -->
                                        <div class="p-4 bg-light border-top">
                                            <div class="d-flex justify-content-between align-items-center total-box">
                                                <div class="total-label">Grand Total Collection</div>
                                                <div class="total-value">₱{{ number_format($grandTotal, 2) }}</div>
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

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
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
</body>

</html>
