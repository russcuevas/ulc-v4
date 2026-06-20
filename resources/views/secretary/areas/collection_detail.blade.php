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
            /* makes the circle solid */
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
            /* Muted orange for the slash */
        }

        /* Breadcrumb Active State (The current page) */
        .breadcrumb-item.active {
            color: #6c757d;
            /* Keep the active one grey so users know where they are */
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
                            <h1 class="m-0">{{ $location_name }} - [{{ $areas_name }}]</h1>

                            <ol class="breadcrumb mt-2">
                                <li class="breadcrumb-item"><a
                                        href="{{ route('secretary.dashboard.page') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item"><a href="{{ route('secretary.areas.page') }}">Areas</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('secretary.areas.collections.references', ['areaId' => $areaId]) }}">{{ $areas_name }}</a>
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

                                <h3 class="card-title">Collection</h3>


                                <button type="button" id="printBtn" class="btn btn-info btn-sm px-3">
                                    <i class="fas fa-print"></i> Print
                                </button>

                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-12 mb-3">
                                    <form id="collectionForm"
                                        action="{{ route('secretary.collections.collect', ['refNo' => $refNo]) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="action" id="actionInput" value="">

                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center bg-white p-3 rounded shadow-sm border-start border-4 border-primary">

                                            <!-- LEFT SIDE (REF + DATE) -->
                                            <div class="mb-2 mb-md-0">
                                                <span class="badge bg-light text-dark border">REF:
                                                    {{ $refNo }}</span>
                                                <h5 class="mb-0 text-dark mt-1">
                                                    <i class="far fa-calendar-alt me-2 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                                                </h5>
                                            </div>

                                            <!-- RIGHT SIDE (BUTTONS) -->
                                            <div class="d-flex flex-column flex-sm-row">
                                                <button type="submit" class="btn btn-success mb-2 mb-sm-0 mr-sm-2"
                                                    data-action="collect">
                                                    <i class="fas fa-hand-holding-usd me-1"></i> Collect Payment
                                                </button>

                                                <button type="submit" class="btn btn-danger mb-2 mb-sm-0 mr-sm-2"
                                                    data-action="no_payment">
                                                    <i class="fas fa-times-circle me-1"></i> No Payment
                                                </button>
                                            </div>

                                        </div>
                                    </form>
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
                                                    <p class="text-muted mb-0 small uppercase">Total Collections
                                                    </p>
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
                                                    <p class="text-muted mb-0 small uppercase">Daily
                                                        Collectibles</p>
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
                                    <button type="button" class="btn btn-outline-primary"
                                        data-filter="normal">Normal Account</button>
                                    <button type="button" class="btn btn-outline-primary"
                                        data-filter="lapsed">Lapsed Account</button>
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

                                            // Overdue only if may balance
                                            $isDangerRow = $hasBalance && $today->greaterThan($loanEnd);

                                            $isPaid = $balance <= 0;
                                        @endphp

                                        <tr class="{{ $isDangerRow ? 'table-danger' : '' }}"
                                            data-status="{{ $isDangerRow ? 'lapsed' : 'normal' }}">
                                            <td>{{ $client->fullname }}</td>

                                            {{-- Due Date --}}
                                            <td>
                                                @if ($client->payment)
                                                    {{ \Carbon\Carbon::parse($client->payment->due_date)->format('Y-m-d') }}
                                                @else
                                                    {{ $selectedDate }}
                                                @endif
                                            </td>

                                            {{-- Balance Should be --}}
                                            @php
                                                $days = $today->lessThan($loanStart)
                                                    ? 0
                                                    : $loanStart->diffInDays($today, false) + 1;
                                                $loanAmount = $client->loan->loan_amount ?? 0;
                                                $daily = $client->loan->daily ?? 0;
                                                $balanceShouldBe = max(0, $loanAmount - $days * $daily);
                                            @endphp
                                            <td>₱{{ number_format($balanceShouldBe, 2) }}</td>

                                            {{-- Overdue --}}
                                            <td>₱{{ number_format($client->overdueVal ?? 0, 2) }}</td>

                                            {{-- Old Balance --}}
                                            <td>₱{{ number_format($client->oldBalanceDisplay ?? 0, 2) }}</td>

                                            {{-- Outstanding Balance --}}
                                            <td>₱{{ number_format($client->outstandingBalanceDisplay ?? 0, 2) }}</td>

                                            {{-- Daily --}}
                                            <td>
                                                @if ($client->payment)
                                                    ₱{{ number_format($client->payment->daily, 2) }}
                                                @else
                                                    ₱{{ number_format($client->loan->daily ?? 0, 2) }}
                                                @endif
                                            </td>

                                            {{-- Collection --}}
                                            <td>
                                                @if ($client->payment && !is_null($client->payment->collection))
                                                    ₱{{ number_format($client->payment->collection, 2) }}
                                                @else
                                                    -
                                                @endif
                                                @if (!$isPaid)
                                                    @if ($client->payment)
                                                        <button type="button"
                                                            class="btn btn-sm btn-warning edit-collection-btn"
                                                            data-payment-id="{{ $client->payment->id }}"
                                                            data-loan-id="{{ $client->loan->id }}"
                                                            data-client-name="{{ $client->fullname }}"
                                                            data-collection="{{ $client->payment->collection ?? '' }}"
                                                            data-type="{{ $client->payment->type ?? 'CASH' }}"
                                                            data-is-collected="{{ $client->payment->is_collected }}"
                                                            title="Edit Collection">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-sm btn-success edit-collection-btn"
                                                            data-payment-id="" data-loan-id="{{ $client->loan->id }}"
                                                            data-client-name="{{ $client->fullname }}"
                                                            data-collection="" data-type="CASH" data-is-collected="0"
                                                            title="Add Collection">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                                @if ($client->payment && (!is_null($client->payment->collection) || !is_null($client->payment->type)))
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger reverse-collection-btn"
                                                        data-payment-id="{{ $client->payment->id }}"
                                                        data-loan-id="{{ $client->loan->id }}"
                                                        data-client-name="{{ $client->fullname }}"
                                                        title="Reverse Collection">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            </td>

                                            {{-- Type --}}
                                            <td>
                                                @if ($client->payment)
                                                    {{ $client->payment->type ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            {{-- STATUS --}}
                                            <td>
                                                @if ($isPaid)
                                                    <span class="badge badge-primary">Paid Loan</span>
                                                @elseif ($client->payment)
                                                    @php
                                                        $col = $client->payment->collection;
                                                        $type = $client->payment->type;
                                                    @endphp

                                                    {{-- If both collection and type are null, show no status --}}
                                                    @if (is_null($col) && is_null($type))
                                                        {{-- blank status --}}
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
                                        <th>
                                            <span id="totalOldBalanceSummary">₱0.00</span>
                                        </th>
                                        <th>
                                            <span id="totalBalanceSummary">₱0.00</span>
                                        </th>
                                        <th>
                                            <span id="totalDailySummary">₱0.00</span>
                                        </th>
                                        <th>
                                            <span id="totalCollectionSummary">₱0.00</span>
                                        </th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
            <!-- /.content -->
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#collectionForm button[type=submit]').click(function(e) {
                $('#actionInput').val($(this).data('action'));
            });

            $('#collectionForm').submit(function(e) {
                let action = $('#actionInput').val();

                // Handle collect, no_payment and reminder via AJAX to avoid full-page JSON responses
                if (action === 'collect' || action === 'no_payment') {
                    e.preventDefault();

                    let title = 'Are you sure?';
                    let text = '';
                    let confirmText = '';

                    if (action === 'collect') {
                        text = 'This will mark all clients with a collection as collected!';
                        confirmText = 'Yes, collect now!';
                    } else if (action === 'no_payment') {
                        text = 'This will tag all clients without payment as NO PAYMENT!';
                        confirmText = 'Yes, mark as NO PAYMENT!';
                    }

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmText
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading alert
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we process your request.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: $(this).attr('action'),
                                method: "POST",
                                data: $(this).serialize(),
                                success: function(response) {
                                    let successTitle = action === 'collect' ?
                                        'Collected!' : 'Tagged!';
                                    Swal.fire({
                                        title: successTitle,
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => location.reload());
                                },
                                error: function(err) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Something went wrong.',
                                        icon: 'error'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#referencesTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "drawCallback": function(settings) {
                    var api = this.api();

                    // Remove formatting to sum numerical values
                    var intVal = function(i) {
                        if (typeof i === 'number') return i;
                        if (typeof i === 'string') {
                            var clean = i.replace(/<[^>]*>/g, '').replace(/[^\d.-]/g, '');
                            var val = parseFloat(clean);
                            return isNaN(val) ? 0 : val;
                        }
                        return 0;
                    };

                    // Sum columns directly from the DOM visible rows
                    var oldBalanceTotal = 0;
                    var balanceTotal = 0;
                    var dailyTotal = 0;
                    var collectionTotal = 0;

                    $('#referencesTable tbody tr').each(function() {
                        // Check if it's not hidden by search filter (search filter hides rows completely, but lazy-hidden is just for pagination)
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

                    // Update summary card values
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

            // Lazy loading variables
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

            // Custom filter using data-status attribute on <tr>
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


        });
    </script>

    <!-- Edit Collection Modal -->
    <div class="modal fade" id="editCollectionModal" tabindex="-1" role="dialog"
        aria-labelledby="editCollectionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCollectionModalLabel">Add/Edit Collection</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>Client:</strong> <span id="editClientName"></span></p>
                    <p class="mb-3 text-muted small" id="editBalanceNote" style="display:none;">
                        <i class="fas fa-info-circle text-info"></i>
                        Since this payment is already
                        <strong>Collected</strong>, changing the collection
                        amount will adjust the client's balance accordingly.
                    </p>
                    <div class="form-group">
                        <label for="editCollectionInput">Collection Amount (₱)</label>
                        <input type="number" id="editCollectionInput" class="form-control" min="0"
                            step="0.01" placeholder="Enter collection amount">
                    </div>
                    <div class="form-group">
                        <label for="editTypeSelect">Payment Type</label>
                        <select id="editTypeSelect" class="form-control">
                            <option value="CASH">CASH</option>
                            <option value="GCASH">GCASH</option>
                            <option value="CHEQUE">CHEQUE</option>
                            <option value="ADVANCE">ADVANCE</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCollectionBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            var currentPaymentId = null;
            var currentLoanId = null;

            $(document).on('click', '.edit-collection-btn', function() {
                currentPaymentId = $(this).data('payment-id');
                currentLoanId = $(this).data('loan-id');
                var clientName = $(this).data('client-name');
                var collection = $(this).data('collection');
                var type = $(this).data('type') || 'CASH';
                var isCollected = parseInt($(this).data('is-collected')) || 0;

                $('#editClientName').text(clientName);
                $('#editCollectionInput').val(collection !== '' ? collection : '');
                $('#editTypeSelect').val(type.toUpperCase());

                if (currentPaymentId) {
                    $('#editCollectionModalLabel').html('<i class="fas fa-edit mr-1"></i> Edit Collection');
                    $('#saveCollectionBtn').text('Save Changes');
                } else {
                    $('#editCollectionModalLabel').html('<i class="fas fa-plus mr-1"></i> Add Collection');
                    $('#saveCollectionBtn').text('Add Collection');
                }

                if (isCollected === 1) {
                    $('#editBalanceNote').show();
                } else {
                    $('#editBalanceNote').hide();
                }

                $('#editCollectionModal').modal('show');
            });

            $('#saveCollectionBtn').on('click', function() {
                var newCollection = $('#editCollectionInput').val();
                var paymentType = $('#editTypeSelect').val();

                if (newCollection === '' || isNaN(newCollection) || parseFloat(newCollection) < 0) {
                    Swal.fire('Invalid Input', 'Please enter a valid collection amount.', 'warning');
                    return;
                }

                $.ajax({
                    url: '{{ route('secretary.collections.payment.save') }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        payment_id: currentPaymentId,
                        loan_id: currentLoanId,
                        reference_number: '{{ $refNo }}',
                        due_date: '{{ $selectedDate }}',
                        client_area: '{{ $areaId }}',
                        collection: parseFloat(newCollection),
                        type: paymentType
                    },
                    success: function(response) {
                        $('#editCollectionModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success').then(function() {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!',
                            'Something went wrong while saving the collection.', 'error');
                    }
                });
            });

            $(document).on('click', '.reverse-collection-btn', function() {
                var paymentId = $(this).data('payment-id');
                var loanId = $(this).data('loan-id');
                var clientName = $(this).data('client-name');

                Swal.fire({
                    title: 'Reverse payment for ' + clientName + '?',
                    text: 'This will reset the collection amount and payment type to null, and adjust the client\'s loan balance accordingly.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, reverse it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Please wait while we process your request.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ route('secretary.collections.payment.reverse') }}',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                payment_id: paymentId,
                                loan_id: loanId,
                                reference_number: '{{ $refNo }}',
                                due_date: '{{ $selectedDate }}',
                                client_area: '{{ $areaId }}'
                            },
                            success: function(response) {
                                Swal.fire('Success!', response.message, 'success').then(
                                    function() {
                                        location.reload();
                                    });
                            },
                            error: function() {
                                Swal.fire('Error!',
                                    'Something went wrong while reversing the collection.',
                                    'error');
                            }
                        });
                    }
                });
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
                    let baseUrl = "{{ route('secretary.collections.print', $refNo) }}";
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
