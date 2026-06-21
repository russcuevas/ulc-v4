<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                            <h1 class="m-0">{{ $location_name }} - [{{ $areas_name }}]</h1>

                            <ol class="breadcrumb mt-2">
                                <li class="breadcrumb-item"><a
                                        href="{{ route('management.dashboard.page') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('management.areas.location.page', ['location' => $location_name]) }}">Areas</a>
                                </li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('management.areas.collections.references', ['areaId' => $areaId]) }}">{{ $areas_name }}</a>
                                </li>
                                <li class="breadcrumb-item active">Reference: {{ $refNo }}</li>
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
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h3 class="card-title">Collection Transaction Details</h3>
                                <button type="button" id="printBtn" class="btn btn-info btn-sm px-3">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-12 mb-3">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center bg-white p-3 rounded shadow-sm border-start border-4 border-primary">
                                        <div>
                                            <span class="badge bg-light text-dark border">REF:
                                                {{ $refNo }}</span>
                                            <h5 class="mb-0 text-dark mt-1">
                                                <i class="far fa-calendar-alt me-2 text-primary"></i>
                                                {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-soft-info p-3 rounded-circle text-info">
                                                    <i class="fas fa-users fa-lg"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-0 small uppercase">Total Clients</p>
                                                    <h4 class="mb-0 fw-bold">{{ $totalClients }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-soft-success p-3 rounded-circle text-success">
                                                    <i class="fas fa-hand-holding-usd fa-lg"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-0 small uppercase">Total Collections</p>
                                                    <h4 class="mb-0 fw-bold text-success">
                                                        ₱{{ number_format($totalCollections, 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-soft-warning p-3 rounded-circle text-warning">
                                                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-0 small uppercase">Daily Collectibles</p>
                                                    <h4 class="mb-0 fw-bold text-dark">
                                                        ₱{{ number_format($totalDailyCollectibles, 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div id="filterTabs" class="btn-group" role="group" aria-label="Filter">
                                    <button type="button" class="btn btn-outline-primary active"
                                        data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="normal">Normal
                                        Account</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="lapsed">Lapsed
                                        Account</button>
                                </div>
                            </div>
                            <input type="hidden" id="currentFilter" value="all">

                            <table id="referencesTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Due Date</th>
                                        <th>Balance Should be</th>
                                        <th>Overdue</th>
                                        <th>Old Balance</th>
                                        <th>Outstanding Balance</th>
                                        <th>Daily</th>
                                        <th>Collection</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clients as $client)
                                        @php
                                            $loanStart = \Carbon\Carbon::parse($client->loan->loan_from);
                                            $loanEnd = \Carbon\Carbon::parse($client->loan->loan_to);
                                            $today = \Carbon\Carbon::parse($selectedDate);
                                            $balance = $client->loan->balance ?? 0;
                                            $hasBalance = $balance > 0;
                                            $isDangerRow = $hasBalance && $today->greaterThan($loanEnd);
                                            $isPaid = $balance <= 0;
                                        @endphp

                                        <tr class="{{ $isDangerRow ? 'table-danger' : '' }}"
                                            data-status="{{ $isDangerRow ? 'lapsed' : 'normal' }}">
                                            <td>{{ $client->fullname }}</td>
                                            <td>
                                                @if ($client->payment)
                                                    {{ \Carbon\Carbon::parse($client->payment->due_date)->format('Y-m-d') }}
                                                @else
                                                    {{ $selectedDate }}
                                                @endif
                                            </td>
                                            @php
                                                $days = $today->lessThan($loanStart)
                                                    ? 0
                                                    : $loanStart->diffInDays($today, false) + 1;
                                                $loanAmount = $client->loan->loan_amount ?? 0;
                                                $daily = $client->loan->daily ?? 0;
                                                $balanceShouldBe = max(0, $loanAmount - $days * $daily);
                                            @endphp
                                            <td>₱{{ number_format($balanceShouldBe, 2) }}</td>
                                            <td>₱{{ number_format($client->overdueVal ?? 0, 2) }}</td>
                                            <td>₱{{ number_format($client->oldBalanceDisplay ?? 0, 2) }}</td>
                                            <td>₱{{ number_format($client->outstandingBalanceDisplay ?? 0, 2) }}</td>
                                            <td>
                                                @if ($client->payment)
                                                    ₱{{ number_format($client->payment->daily, 2) }}
                                                @else
                                                    ₱{{ number_format($client->loan->daily ?? 0, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($client->payment && !is_null($client->payment->collection))
                                                    ₱{{ number_format($client->payment->collection, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($client->payment)
                                                    {{ $client->payment->type ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isPaid)
                                                    <span class="badge badge-primary">Paid Loan</span>
                                                @elseif ($client->payment)
                                                    @php
                                                        $col = $client->payment->collection;
                                                        $type = $client->payment->type;
                                                    @endphp
                                                    @if (is_null($col) && is_null($type))
                                                        {{-- blank --}}
                                                    @elseif ($type === 'NO PAYMENT')
                                                        <span class="badge badge-danger">No Payment</span>
                                                    @elseif ($client->payment->is_collected == 1)
                                                        <span class="badge badge-success">Collected</span>
                                                    @elseif (!is_null($col) && $col > 0 && $client->payment->is_collected == 0)
                                                        <span class="badge badge-info">To Collect</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4"
                                            style="text-align: right; vertical-align: middle; font-weight: bold; font-size: 1.1rem;">
                                            Total:</th>
                                        <th><span id="totalOldBalanceSummary">₱0.00</span></th>
                                        <th><span id="totalBalanceSummary">₱0.00</span></th>
                                        <th><span id="totalDailySummary">₱0.00</span></th>
                                        <th><span id="totalCollectionSummary">₱0.00</span></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('dist/js/demo.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var table = $('#referencesTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "drawCallback": function(settings) {
                    var api = this.api();
                    var intVal = function(i) {
                        if (typeof i === 'number') return i;
                        if (typeof i === 'string') {
                            var clean = i.replace(/<[^>]*>/g, '').replace(/[^\d.-]/g, '');
                            var val = parseFloat(clean);
                            return isNaN(val) ? 0 : val;
                        }
                        return 0;
                    };

                    var oldBalanceTotal = 0;
                    var balanceTotal = 0;
                    var dailyTotal = 0;
                    var collectionTotal = 0;

                    $('#referencesTable tbody tr').each(function() {
                        if ($(this).css('display') !== 'none' || $(this).hasClass(
                            'lazy-hidden')) {
                            var cells = $(this).find('td');
                            if (cells.length >= 8) {
                                oldBalanceTotal += intVal($(cells[4]).html());
                                balanceTotal += intVal($(cells[5]).html());
                                dailyTotal += intVal($(cells[6]).html());
                                collectionTotal += intVal($(cells[7]).html());
                            }
                        }
                    });

                    $('#totalOldBalanceSummary').html('₱' + oldBalanceTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#totalBalanceSummary').html('₱' + balanceTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#totalDailySummary').html('₱' + dailyTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#totalCollectionSummary').html('₱' + collectionTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                }
            });

            var visibleCount = 15;
            var isLoadPending = false;
            var loaderId = 'referencesTable_lazyLoader';

            var loaderHtml = '<div id="' + loaderId +
                '" class="text-center py-3 lazy-loader-indicator" style="display: none;">' +
                '<div class="spinner-border text-primary" role="status" style="color: #FF5F00 !important;">' +
                '<span class="sr-only">Loading...</span>' +
                '</div>' +
                '</div>';
            $('#referencesTable').after(loaderHtml);

            if ($('#lazy-hidden-style').length === 0) {
                $('head').append(
                '<style id="lazy-hidden-style">.lazy-hidden { display: none !important; }</style>');
            }

            function applyLazyLoading() {
                var matchingRows = $('#referencesTable tbody tr').filter(function() {
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
                    if ($('#referencesTable tbody tr.lazy-hidden').length > 0) {
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
                visibleCount = 15;
                applyLazyLoading();
            });

            applyLazyLoading();

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'referencesTable') return true;
                var filter = $('#currentFilter').val();
                if (!filter || filter === 'all') return true;
                var node = table.row(dataIndex).node();
                var status = $(node).data('status');
                if (filter === 'lapsed') return status === 'lapsed';
                if (filter === 'normal') return status === 'normal';
                return true;
            });

            $('#filterTabs button').on('click', function() {
                var filter = $(this).data('filter');
                $('#filterTabs button').removeClass('active');
                $(this).addClass('active');
                $('#currentFilter').val(filter);
                table.draw();
            });

            $('#printBtn').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Print Collection',
                    text: 'Select which collection report you want to print:',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Print Normal Collection',
                    denyButtonText: 'Print Lapsed Collection',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    denyButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    let baseUrl = "{{ route('management.collections.print', $refNo) }}";
                    if (result.isConfirmed) {
                        window.open(baseUrl + '?type=normal', '_blank');
                    } else if (result.isDenied) {
                        window.open(baseUrl + '?type=lapsed', '_blank');
                    }
                });
            });
        });
    </script>
</body>

</html>
