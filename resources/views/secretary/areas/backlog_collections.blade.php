<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backlog Collections - {{ $client->fullname }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
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

        .nav-sidebar .nav-treeview .nav-link.active {
            background-color: transparent !important;
            color: inherit !important;
        }

        .nav-sidebar .nav-treeview .nav-link.active .nav-icon.fa-circle {
            color: #FF5F00 !important;
            font-weight: 900;
        }

        .card-primary.card-outline {
            border-top: 3px solid #FF5F00;
        }

        .soa-box {
            border: 1px solid #000;
            padding: 15px;
            background: #fff;
            margin-bottom: 20px;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .right-info {
            text-align: right;
            font-size: 11px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .info-box-custom {
            width: 48%;
            font-size: 13px;
        }

        .line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            font-weight: bold;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #000 !important;
            padding: 5px;
            text-align: center;
            font-size: 11px;
        }

        .summary-table th {
            background: #eaeaea;
        }

        .grid-table th {
            background: #eaeaea;
            font-size: 11px;
            border: 1px solid #000 !important;
            vertical-align: middle !important;
        }

        .grid-table td {
            font-size: 11px;
            border: 1px solid #000 !important;
            vertical-align: middle !important;
        }

        .badge-od {
            background-color: #f8d7da;
            color: #721c24;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-action-group {
            display: flex;
            gap: 4px;
            justify-content: center;
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
                            <h1 class="m-0">Backlog Collections Manager</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <a href="{{ route('secretary.area.clients.loans', $client->id) }}"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Client Loans
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-list-alt mr-1"></i>
                                        Interactive Statement of Account & Collections Grid
                                    </h3>
                                </div>

                                <div class="card-body">
                                    <div class="soa-box">
                                        <!-- HEADER -->
                                        <div class="top-header">
                                            <div class="title">
                                                {{ strtoupper($client->fullname) }}
                                            </div>

                                            <div class="right-info">
                                                For any concern, Please contact:<br>
                                                Mobile No.: 0995-418-1658<br>
                                                <strong>JESSA A. MISAJON - OIC</strong>
                                            </div>
                                        </div>

                                        <!-- DETAILS -->
                                        <div class="info-row">
                                            <div class="info-box-custom">
                                                NAME: <span class="line">{{ $client->fullname }}</span>
                                            </div>

                                            <div class="info-box-custom">
                                                DATE: <span class="line">{{ now()->format('M d, Y') }}</span>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-box-custom">
                                                PN#: <span class="line">{{ $loan->pn_number }}</span>
                                            </div>

                                            <div class="info-box-custom">
                                                DURATION:
                                                <span class="line">
                                                    {{ \Carbon\Carbon::parse($loan->loan_from)->format('M d, Y') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-box-custom">
                                                FC: <span class="line">N/A</span>
                                            </div>
                                        </div>

                                        <!-- LOAN SUMMARY -->
                                        <table class="table summary-table mt-3">
                                            <thead>
                                                <tr>
                                                    <th>PN AMOUNT</th>
                                                    <th>DUE DATE</th>
                                                    <th>TERMS</th>
                                                    <th>TOTAL SAVINGS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>₱{{ number_format($loan->loan_amount, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}
                                                    </td>
                                                    <td>{{ $loan->loan_terms ?? 'N/A' }}</td>
                                                    <td>₱{{ number_format(collect($paymentsGrid)->take(100)->sum('savings_amount'), 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- INTERACTIVE GRID -->
                                    <div class="table-responsive">
                                        <table
                                            class="table table-bordered table-striped table-hover grid-table text-center">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Date</th>
                                                    <th>Balance Should be</th>
                                                    <th>Outstanding Balance</th>
                                                    <th>Daily Payment</th>
                                                    <th>Total Payment</th>
                                                    <th>Savings</th>
                                                    <th>Daily OD</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($paymentsGrid as $item)
                                                    @if ($item->index > 100)
                                                        @continue
                                                    @endif
                                                    @php
                                                        $dueDate = \Carbon\Carbon::parse($item->date);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $item->index }}</td>
                                                        <td>{{ $dueDate->format('n/d/Y') }}</td>
                                                        <td>₱{{ number_format($item->balance_should_be, 2) }}</td>
                                                        <td>₱{{ number_format($item->outstanding_balance, 2) }}</td>
                                                        <td>
                                                            @if ($item->is_collected)
                                                                <span class="badge bg-success" style="font-size: 11px;">
                                                                    ₱{{ number_format($item->collection, 2) }}
                                                                    ({{ $item->type }})
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>₱{{ number_format($item->total_payment, 2) }}</td>
                                                        <td>
                                                            @if (is_numeric($item->savings_amount) && $item->savings_amount > 0)
                                                                <span class="badge bg-info" style="font-size: 11px;">
                                                                    ₱{{ number_format($item->savings_amount, 2) }}
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->daily_od > 0)
                                                                <span class="badge-od">
                                                                    ₱{{ number_format($item->daily_od, 2) }}
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-action-group">
                                                                <!-- Payment Actions -->
                                                                @if ($item->is_collected)
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-warning edit-collection-btn"
                                                                        data-payment-id="{{ $item->payment_id }}"
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-collection="{{ $item->collection }}"
                                                                        data-type="{{ $item->type }}"
                                                                        data-is-collected="1"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Edit Collection">
                                                                        <i class="fas fa-edit"></i> Edit Pay
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-danger reverse-collection-btn"
                                                                        data-payment-id="{{ $item->payment_id }}"
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Reverse Collection">
                                                                        <i class="fas fa-undo"></i> Rev Pay
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-success edit-collection-btn"
                                                                        data-payment-id=""
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-collection="" data-type="CASH"
                                                                        data-is-collected="0"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Add Collection">
                                                                        <i class="fas fa-plus"></i> Pay
                                                                    </button>
                                                                @endif

                                                                <!-- Savings Actions -->
                                                                @if (is_numeric($item->savings_amount) && $item->savings_amount > 0)
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-info edit-savings-btn"
                                                                        data-payment-id="{{ $item->payment_id }}"
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-savings="{{ $item->savings_amount }}"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Edit Savings">
                                                                        <i class="fas fa-wallet"></i> Edit Sav
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-danger reverse-savings-btn"
                                                                        data-payment-id="{{ $item->payment_id }}"
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Reverse Savings">
                                                                        <i class="fas fa-undo"></i> Rev Sav
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-outline-info edit-savings-btn"
                                                                        data-payment-id="{{ $item->payment_id }}"
                                                                        data-loan-id="{{ $loan->id }}"
                                                                        data-client-name="{{ $client->fullname }}"
                                                                        data-savings="0"
                                                                        data-due-date="{{ $item->date }}"
                                                                        data-reference-number="{{ $item->reference_number }}"
                                                                        title="Add Savings">
                                                                        <i class="fas fa-plus"></i> Sav
                                                                    </button>
                                                                @endif
                                                            </div>
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
                </div>
            </section>
        </div>
    </div>

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
                    <p class="mb-2"><strong>Due Date:</strong> <span id="editDueDateDisplay"></span></p>
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
                            <option value="BANK TRANSFER">BANK TRANSFER</option>
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

    <!-- Edit Savings Modal -->
    <div class="modal fade" id="editSavingsModal" tabindex="-1" role="dialog"
        aria-labelledby="editSavingsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSavingsModalLabel">Add/Edit Savings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>Client:</strong> <span id="editSavingsClientName"></span></p>
                    <p class="mb-2"><strong>Due Date:</strong> <span id="editSavingsDueDateDisplay"></span></p>
                    <div class="form-group">
                        <label for="editSavingsInput">Savings Amount (₱)</label>
                        <input type="number" id="editSavingsInput" class="form-control" min="0"
                            step="0.01" placeholder="Enter savings amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSavingsBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED SCRIPTS -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            var currentPaymentId = null;
            var currentLoanId = null;
            var currentDueDate = null;
            var currentReferenceNumber = null;

            // Open Payment Modal
            $(document).on('click', '.edit-collection-btn', function() {
                currentPaymentId = $(this).data('payment-id');
                currentLoanId = $(this).data('loan-id');
                currentDueDate = $(this).data('due-date');
                currentReferenceNumber = $(this).data('reference-number');

                var clientName = $(this).data('client-name');
                var collection = $(this).data('collection');
                var type = $(this).data('type') || 'CASH';
                var isCollected = parseInt($(this).data('is-collected')) || 0;

                $('#editClientName').text(clientName);
                $('#editDueDateDisplay').text(currentDueDate);
                $('#editCollectionInput').val(collection !== '' ? collection : '');
                $('#editTypeSelect').val(type.toUpperCase());

                if (currentPaymentId) {
                    $('#editCollectionModalLabel').html('<i class="fas fa-edit mr-1"></i> Edit Collection');
                    $('#saveCollectionBtn').text('Save Changes');
                    $('#editBalanceNote').show();
                } else {
                    $('#editCollectionModalLabel').html('<i class="fas fa-plus mr-1"></i> Add Collection');
                    $('#saveCollectionBtn').text('Add Collection');
                    $('#editBalanceNote').hide();
                }

                $('#editCollectionModal').modal('show');
            });

            // Save Payment
            $('#saveCollectionBtn').on('click', function() {
                var newCollection = $('#editCollectionInput').val();
                var paymentType = $('#editTypeSelect').val();

                if (newCollection === '' || isNaN(newCollection) || parseFloat(newCollection) < 0) {
                    Swal.fire('Invalid Input', 'Please enter a valid collection amount.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('secretary.collections.payment.save') }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        payment_id: currentPaymentId,
                        loan_id: currentLoanId,
                        reference_number: currentReferenceNumber,
                        due_date: currentDueDate,
                        client_area: '{{ $client->area_id }}',
                        collection: parseFloat(newCollection),
                        type: paymentType
                    },
                    success: function(response) {
                        $('#editCollectionModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success').then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var msg = 'Something went wrong while saving the collection.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            });

            // Reverse Payment
            $(document).on('click', '.reverse-collection-btn', function() {
                var paymentId = $(this).data('payment-id');
                var loanId = $(this).data('loan-id');
                var clientName = $(this).data('client-name');
                var dueDate = $(this).data('due-date');
                var referenceNumber = $(this).data('reference-number');

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
                            allowOutsideClick: false,
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
                                reference_number: referenceNumber,
                                due_date: dueDate,
                                client_area: '{{ $client->area_id }}'
                            },
                            success: function(response) {
                                Swal.fire('Success!', response.message, 'success').then(
                                    function() {
                                        location.reload();
                                    });
                            },
                            error: function(xhr) {
                                var msg =
                                    'Something went wrong while reversing the collection.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // Open Savings Modal
            var currentSavingsPaymentId = null;
            var currentSavingsLoanId = null;
            var currentSavingsDueDate = null;
            var currentSavingsReferenceNumber = null;

            $(document).on('click', '.edit-savings-btn', function() {
                currentSavingsPaymentId = $(this).data('payment-id');
                currentSavingsLoanId = $(this).data('loan-id');
                currentSavingsDueDate = $(this).data('due-date');
                currentSavingsReferenceNumber = $(this).data('reference-number');

                var clientName = $(this).data('client-name');
                var savings = $(this).data('savings') || 0;

                $('#editSavingsClientName').text(clientName);
                $('#editSavingsDueDateDisplay').text(currentSavingsDueDate);
                $('#editSavingsInput').val(savings > 0 ? savings : '');

                if (currentSavingsPaymentId) {
                    $('#editSavingsModalLabel').html('<i class="fas fa-edit mr-1"></i> Edit Savings');
                    $('#saveSavingsBtn').text('Save Changes');
                } else {
                    $('#editSavingsModalLabel').html('<i class="fas fa-plus mr-1"></i> Add Savings');
                    $('#saveSavingsBtn').text('Add Savings');
                }

                $('#editSavingsModal').modal('show');
            });

            // Save Savings
            $('#saveSavingsBtn').on('click', function() {
                var newSavings = $('#editSavingsInput').val();

                if (newSavings === '' || isNaN(newSavings) || parseFloat(newSavings) < 0) {
                    Swal.fire('Invalid Input', 'Please enter a valid savings amount.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('secretary.collections.savings.save') }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        payment_id: currentSavingsPaymentId,
                        loan_id: currentSavingsLoanId,
                        reference_number: currentSavingsReferenceNumber,
                        due_date: currentSavingsDueDate,
                        client_area: '{{ $client->area_id }}',
                        savings_amount: parseFloat(newSavings)
                    },
                    success: function(response) {
                        $('#editSavingsModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success').then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var msg = 'Something went wrong while saving the savings.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            });

            // Reverse Savings
            $(document).on('click', '.reverse-savings-btn', function() {
                var paymentId = $(this).data('payment-id');
                var loanId = $(this).data('loan-id');
                var clientName = $(this).data('client-name');
                var dueDate = $(this).data('due-date');
                var referenceNumber = $(this).data('reference-number');

                Swal.fire({
                    title: 'Reverse savings for ' + clientName + '?',
                    text: 'This will reset the savings amount to 0.00, and adjust the client\'s loan savings balance accordingly.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, reverse it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ route('secretary.collections.savings.reverse') }}',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                payment_id: paymentId,
                                loan_id: loanId,
                                reference_number: referenceNumber,
                                due_date: dueDate,
                                client_area: '{{ $client->area_id }}'
                            },
                            success: function(response) {
                                Swal.fire('Success!', response.message, 'success').then(
                                    function() {
                                        location.reload();
                                    });
                            },
                            error: function(xhr) {
                                var msg =
                                    'Something went wrong while reversing the savings.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
