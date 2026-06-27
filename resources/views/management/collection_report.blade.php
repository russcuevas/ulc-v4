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
                                                    <span class="location-name mr-3">{{ $item->location_name }}</span>
                                                    <button class="btn btn-xs btn-outline-primary no-print px-2" data-toggle="modal" data-target="#breakdownModal-{{ Str::slug($item->location_name) }}" style="border-radius: 4px;">
                                                        <i class="fas fa-list-alt mr-1"></i> View Breakdown
                                                    </button>
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

    <!-- Breakdown Modals -->
    @foreach($report as $item)
        @php
            $locSlug = Str::slug($item->location_name);
            $locBreakdown = $breakdownByLocation->get($item->location_name) ?? collect();
        @endphp
        <div class="modal fade" id="breakdownModal-{{ $locSlug }}" tabindex="-1" role="dialog" aria-labelledby="breakdownModalLabel-{{ $locSlug }}" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: none;">
                    <div class="modal-header bg-light" style="border-bottom: 1px solid #dee2e6; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold text-dark" id="breakdownModalLabel-{{ $locSlug }}">
                            <i class="fas fa-chart-pie text-primary mr-2"></i>{{ $item->location_name }} Breakdown - {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                        
                        <!-- Area-level breakdown -->
                        <h5 class="font-weight-bold text-secondary mb-3"><i class="fas fa-map-marker-alt text-primary mr-2"></i>Area Summaries</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Area Code</th>
                                        <th>Assigned Collector</th>
                                        <th class="text-right">Total Collectibles</th>
                                        <th class="text-right">Total Collected</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locBreakdown as $areaName => $areaData)
                                        @php
                                            $collectedRate = $areaData->total_collectibles > 0 ? ($areaData->total_collected / $areaData->total_collectibles) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold">{{ $areaName }}</td>
                                            <td>{{ $areaData->assigned_collector ?? 'Unassigned' }}</td>
                                            <td class="text-right">₱{{ number_format($areaData->total_collectibles, 2) }}</td>
                                            <td class="text-right text-success font-weight-bold">₱{{ number_format($areaData->total_collected, 2) }}</td>
                                            <td class="text-center">
                                                @if($collectedRate >= 100)
                                                    <span class="badge badge-success px-2 py-1">Fully Collected</span>
                                                @elseif($collectedRate > 0)
                                                    <span class="badge badge-warning px-2 py-1">{{ number_format($collectedRate, 1) }}%</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1">No Payments</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No area breakdown details found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Client-level collections details -->
                        <h5 class="font-weight-bold text-secondary mb-3 mt-4"><i class="fas fa-users text-primary mr-2"></i>Detailed Collections</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered client-breakdown-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Area</th>
                                        <th>Client Name</th>
                                        <th>Reference Number</th>
                                        <th class="text-right">Daily</th>
                                        <th class="text-right">Collection</th>
                                        @if($item->location_name === 'Financial Counselor')
                                            <th class="text-right">Savings</th>
                                        @endif
                                        <th class="text-center">Type</th>
                                        <th>Collected By</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasPayments = false; @endphp
                                    @foreach($locBreakdown as $areaName => $areaData)
                                        @foreach($areaData->payments as $payment)
                                            @php $hasPayments = true; @endphp
                                            <tr>
                                                <td>{{ $areaName }}</td>
                                                <td class="font-weight-bold text-dark">{{ $payment->client_name }}</td>
                                                <td><code>{{ $payment->reference_number }}</code></td>
                                                <td class="text-right">₱{{ number_format($payment->daily, 2) }}</td>
                                                <td class="text-right font-weight-bold {{ $payment->is_collected == 1 ? 'text-success' : 'text-muted' }}">
                                                    ₱{{ number_format($payment->collection, 2) }}
                                                </td>
                                                @if($item->location_name === 'Financial Counselor')
                                                    <td class="text-right text-info">₱{{ number_format($payment->savings_amount, 2) }}</td>
                                                @endif
                                                <td class="text-center"><span class="badge bg-light text-dark border">{{ $payment->type ?? '-' }}</span></td>
                                                <td>{{ $payment->collector_name ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    @if($payment->is_collected == 1)
                                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Collected</span>
                                                    @else
                                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach

                                    @if(!$hasPayments)
                                        <tr>
                                            <td colspan="{{ $item->location_name === 'Financial Counselor' ? 9 : 8 }}" class="text-center text-muted">No individual payment records found for this date.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid #dee2e6; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

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

        $(function() {
            // When any breakdown modal is shown, initialize lazy loading
            $('.modal').on('shown.bs.modal', function() {
                var modal = $(this);
                var modalBody = modal.find('.modal-body');
                var table = modal.find('.client-breakdown-table');
                var tbody = table.find('tbody');
                var rows = tbody.find('tr');
                
                // If already initialized, skip
                if (modal.data('lazy-loaded') === true) {
                    return;
                }
                modal.data('lazy-loaded', true);
                
                var initialShow = 15;
                var increment = 15;
                var visibleCount = initialShow;
                
                // Hide all rows initially beyond initialShow
                if (rows.length > visibleCount) {
                    rows.slice(visibleCount).addClass('lazy-hidden').hide();
                    
                    // Add a spinner element below the table
                    var spinnerId = 'spinner-' + modal.attr('id');
                    var spinnerHtml = '<div id="' + spinnerId + '" class="text-center py-3 modal-lazy-loader" style="display: none;">' +
                                      '<div class="spinner-border text-primary" role="status" style="color: #FF5F00 !important;">' +
                                      '<span class="sr-only">Loading...</span>' +
                                      '</div>' +
                                      '</div>';
                    table.after(spinnerHtml);
                    var spinner = $('#' + spinnerId);
                    
                    // Flag to prevent multiple concurrent triggers
                    var isLoadPending = false;
                    
                    modalBody.on('scroll', function() {
                        if (isLoadPending) return;
                        
                        // Check if user has scrolled near the bottom of modalBody
                        var scrollHeight = modalBody[0].scrollHeight;
                        var scrollTop = modalBody.scrollTop();
                        var clientHeight = modalBody[0].clientHeight;
                        
                        if (scrollTop + clientHeight >= scrollHeight - 30) {
                            var hiddenRows = tbody.find('tr.lazy-hidden');
                            if (hiddenRows.length > 0) {
                                isLoadPending = true;
                                spinner.show();
                                
                                setTimeout(function() {
                                    // Show next batch of rows
                                    var nextBatch = hiddenRows.slice(0, increment);
                                    nextBatch.removeClass('lazy-hidden').fadeIn(200);
                                    
                                    spinner.hide();
                                    isLoadPending = false;
                                    
                                    // If no more hidden rows, remove scroll event listener and spinner
                                    if (tbody.find('tr.lazy-hidden').length === 0) {
                                        spinner.remove();
                                    }
                                }, 400); // 400ms delay for nice visual effect
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
