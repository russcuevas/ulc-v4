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
        @include('collector.components.topbar')
        {{-- END TOP BAR --}}

        {{-- LEFT SIDEBAR --}}
        @include('collector.components.sidebar')
        {{-- END LEFT SIDEBAR --}}

        {{-- MAIN --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                {{ $area->location_name ?? 'N/A' }} - [{{ $area->areas_name ?? 'N/A' }}]
                            </h1>
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
                                    <div class="d-flex justify-content-between align-items-center w-100">

                                        <h3 class="card-title">{{ $area->location_name ?? 'N/A' }} -
                                            [{{ $area->areas_name ?? 'N/A' }}]</h3>


                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            @if ($myAreas->count() > 1)
                                                <div class="input-group input-group-sm mr-2"
                                                    style="width: auto; max-width: 250px;">
                                                    <div class="input-group-prepend">
                                                        <span
                                                            class="input-group-text bg-primary text-white border-primary"
                                                            style="background-color: #FF5F00 !important; border-color: #FF5F00 !important;">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                        </span>
                                                    </div>
                                                    <select id="areaSelect"
                                                        class="form-control form-control-sm border-primary"
                                                        style="font-weight: 600; color: #333; border-color: #FF5F00 !important;">
                                                        @foreach ($myAreas as $a)
                                                            <option value="{{ $a->id }}"
                                                                {{ $selectedAreaId == $a->id ? 'selected' : '' }}>
                                                                {{ $a->location_name }} - [{{ $a->areas_name }}]
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <span id="bulkTotalDisplay" class="badge badge-warning py-2 px-3"
                                                style="font-size: 13px; font-weight: 600; background-color: #FF5F00; color: #fff; border-radius: 4px; display: none;">Total:
                                                ₱0.00</span>

                                            <button type="button" id="bulkSaveBtn" class="btn btn-primary btn-sm px-3">
                                                <i class="fas fa-save"></i>&nbsp; Bulk Save All
                                            </button>

                                            <button class="btn btn-success btn-sm px-3" data-toggle="modal"
                                                data-target="#selectDate">
                                                <i class="fas fa-calendar"></i>&nbsp; Select Date
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                {{-- add collectibles modal --}}
                                @include('collector.collection.modals.select_date')
                                @include('collector.collection.modals.no_payment')
                                @include('collector.collection.modals.view_proof')
                                <div class="card-body">
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 mb-3">
                                            <div
                                                class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm border-start border-4 border-primary">
                                                <div><span class="badge bg-light text-dark border">REF:
                                                        {{ $refNo }}</span>
                                                    <h5 class="mb-0 text-dark">
                                                        <i class="far fa-calendar-alt me-2 text-primary"></i>
                                                        {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $isFC =
                                                stripos($area->location_name ?? '', 'Financial Counselor') !== false;
                                            $colClass = $isFC ? 'col-md-3' : 'col-md-4';
                                        @endphp

                                        <div class="{{ $colClass }}">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 bg-soft-info p-3 rounded-circle text-info">
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

                                        <div class="{{ $colClass }}">
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

                                        <div class="{{ $colClass }}">
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

                                        @if ($isFC)
                                            <div class="{{ $colClass }}">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 p-3 rounded-circle"
                                                                style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                                                <i class="fas fa-piggy-bank fa-lg"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <p class="text-muted mb-0 small uppercase">Total Savings
                                                                </p>
                                                                <h4 class="mb-0 fw-bold text-primary">
                                                                    ₱{{ number_format($totalSavings, 2) }}</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div id="filterTabs" class="btn-group" role="group" aria-label="Filter">
                                            <button type="button" class="btn btn-outline-primary active"
                                                data-filter="normal">Normal Account</button>
                                            <button type="button" class="btn btn-outline-primary"
                                                data-filter="lapsed">Lapsed Account</button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="currentFilter" value="normal">
                                    @csrf
                                    <div class="table-responsive">
                                        <table id="manilaTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Client Name</th>
                                                    <th>Due Date</th>
                                                    <th>Balance</th>
                                                    <th>Daily</th>
                                                    <th>Collection</th>
                                                    @if (stripos($area->location_name ?? '', 'Financial Counselor') !== false)
                                                        <th>Savings</th>
                                                    @endif
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($clients as $client)
                                                    <tr class="{{ $client->is_overdue ? 'table-danger' : '' }}"
                                                        data-status="{{ $client->is_overdue ? 'lapsed' : 'normal' }}"
                                                        data-client-id="{{ $client->id }}"
                                                        data-loan-id="{{ $client->loan->id ?? '' }}"
                                                        data-area-id="{{ $client->area_id }}"
                                                        data-client-name="{{ $client->fullname }}"
                                                        data-balance="{{ $client->loan->balance ?? 0 }}"
                                                        data-daily="{{ $client->loan->daily ?? 0 }}">
                                                        <td>{{ $client->fullname }}</td>

                                                        {{-- Due Date --}}
                                                        <td>
                                                            @if ($client->payment)
                                                                {{ \Carbon\Carbon::parse($client->payment->due_date)->format('Y-m-d') }}
                                                            @else
                                                                <input type="date" value="{{ $selectedDate }}"
                                                                    readonly class="form-control">
                                                            @endif
                                                        </td>

                                                        {{-- Balance --}}
                                                        <td>
                                                            ₱{{ number_format($client->loan->balance ?? 0, 2) }}
                                                        </td>

                                                        {{-- Daily --}}
                                                        <td>
                                                            @if ($client->payment)
                                                                ₱{{ number_format($client->payment->daily, 2) }}
                                                            @else
                                                                ₱{{ number_format($client->loan->daily ?? 0, 2) }}
                                                            @endif
                                                        </td>

                                                        {{-- Collection --}}
                                                        @if ($client->payment && !(is_null($client->payment->collection) && is_null($client->payment->type)))
                                                            <td>
                                                                @if (is_null($client->payment->collection) || $client->payment->collection == 0 || $client->payment->type === 'NO PAYMENT')
                                                                    0
                                                                @else
                                                                    ₱{{ number_format($client->payment->collection, 2) }}
                                                                @endif
                                                            </td>

                                                            @if (stripos($area->location_name ?? '', 'Financial Counselor') !== false)
                                                                <td>
                                                                    ₱{{ number_format($client->payment->savings_amount ?? 0, 2) }}
                                                                </td>
                                                            @endif

                                                            <td>
                                                                {{ $client->payment->type ?? '-' }}
                                                            </td>

                                                            {{-- STATUS --}}
                                                            <td>
                                                                @if ($client->isPaid)
                                                                    <span class="badge badge-primary">Paid Loan</span>
                                                                @elseif ($client->payment->type === 'NO PAYMENT' || $client->payment->collection == 0)
                                                                    <span class="badge badge-danger">NO PAYMENT</span>
                                                                    @if (!empty($client->payment->image) || !empty($client->payment->remarks))
                                                                        <div class="mt-1">
                                                                            <button type="button" class="btn btn-xs btn-outline-danger view-proof-btn"
                                                                                data-client-name="{{ $client->fullname }}"
                                                                                data-image="{{ !empty($client->payment->image) ? asset($client->payment->image) : '' }}"
                                                                                data-remarks="{{ $client->payment->remarks ?? '' }}">
                                                                                <i class="fas fa-image mr-1"></i> View Proof
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @elseif ($client->payment->is_collected == 1)
                                                                    <span class="badge badge-success">Collected</span>
                                                                @else
                                                                    <span class="badge badge-info">To Collect</span>
                                                                @endif
                                                            </td>
                                                        @else
                                                            <td>
                                                                <form
                                                                    action="{{ route('collector.collections.store') }}"
                                                                    method="POST"
                                                                    enctype="multipart/form-data">
                                                                    @csrf
                                                                    <input type="number" step="0.01"
                                                                        name="collection" class="form-control"
                                                                        min="1"
                                                                        placeholder="Enter amount if any">
                                                            </td>

                                                            @if (stripos($area->location_name ?? '', 'Financial Counselor') !== false)
                                                                <td>
                                                                    <input type="number" step="0.01"
                                                                        name="savings" class="form-control"
                                                                        min="0"
                                                                        placeholder="Enter savings if any">
                                                                </td>
                                                            @endif

                                                            <td>
                                                                <select name="type" class="form-control collection-type-select" required>
                                                                    <option value="">Select</option>
                                                                    <option value="CASH">CASH</option>
                                                                    <option value="GCASH">GCASH</option>
                                                                    <option value="CHEQUE">CHEQUE</option>
                                                                    <option value="BANK TRANSFER">BANK TRANSFER</option>
                                                                    <option value="NO PAYMENT">NO PAYMENT</option>
                                                                </select>
                                                            </td>

                                                            <td>
                                                                <button type="submit" class="btn btn-success btn-sm btn-save-row">
                                                                    Save collection
                                                                </button>

                                                                {{-- Hidden Fields --}}
                                                                <input type="hidden" name="client_id"
                                                                    value="{{ $client->id }}">
                                                                <input type="hidden" name="loan_id"
                                                                    value="{{ $client->loan->id ?? '' }}">
                                                                <input type="hidden" name="area_id"
                                                                    value="{{ $client->area_id }}">
                                                                <input type="hidden" name="reference_no"
                                                                    value="{{ $refNo }}">
                                                                <input type="hidden" name="due_date"
                                                                    value="{{ $selectedDate }}">
                                                                <input type="hidden" name="old_balance"
                                                                    value="{{ $client->loan->balance ?? 0 }}">
                                                                <input type="hidden" name="daily"
                                                                    value="{{ $client->loan->daily ?? 0 }}">
                                                                </form>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="lazyLoader" class="text-center py-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status"
                                            style="color: #FF5F00 !important;">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        let notyf;
        document.addEventListener("DOMContentLoaded", function() {

            notyf = new Notyf({
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

            var table = $('#manilaTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "responsive": false
            });

            // Custom filter using data-status attribute on <tr>
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'manilaTable') return true;
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

            // Lazy loading variables
            var visibleCount = 30;
            var isLoadPending = false;

            function applyLazyLoading() {
                var matchingRows = $('#manilaTable tbody tr').filter(function() {
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
                    if ($('#manilaTable tbody tr.lazy-hidden').length > 0) {
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

            function updateBulkTotal() {
                const rows = table.rows().nodes();
                let total = 0;
                $(rows).each(function() {
                    const collectionInput = $(this).find('input[name="collection"]');
                    if (collectionInput.length) {
                        const val = parseFloat(collectionInput.val());
                        if (!isNaN(val) && val > 0) {
                            total += val;
                        }
                    }
                });

                if (total > 0) {
                    $('#bulkTotalDisplay').text('Total: ₱' + total.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })).show();
                } else {
                    $('#bulkTotalDisplay').hide();
                }
            }

            $(document).on('input', 'input[name="collection"]', function() {
                updateBulkTotal();
            });

            let currentActiveNoPaymentRow = null;
            let isSubmittingNoPayment = false;

            function openNoPaymentModal(tr) {
                currentActiveNoPaymentRow = tr;
                const clientId = tr.data('client-id');
                const loanId = tr.data('loan-id');
                const areaId = tr.data('area-id');
                const clientName = tr.data('client-name');
                const daily = tr.data('daily');
                const balance = tr.data('balance');

                $('#np_modal_client_name').text(clientName || '-');
                $('#np_modal_daily').text('₱' + parseFloat(daily || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#np_modal_balance').text('₱' + parseFloat(balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                $('#np_client_id').val(clientId);
                $('#np_loan_id').val(loanId);
                $('#np_area_id').val(areaId);
                $('#np_daily').val(daily);
                $('#np_old_balance').val(balance);

                // Reset modal inputs
                $('#np_image').val('');
                $('#np_image_label').text('Take photo or choose image...');
                $('#np_remarks').val('');
                $('#np_preview_container').hide();
                $('#np_preview_img').attr('src', '');
                $('#npSubmitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Collection');
                isSubmittingNoPayment = false;

                $('#noPaymentModal').modal('show');
            }

            // Helper function to format bytes
            function formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
            }

            // Client-side image compression using HTML5 Canvas
            function compressImageFile(file, maxDimension, quality, callback) {
                if (!file || !file.type.startsWith('image/')) {
                    callback(file, null, null);
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        let width = img.width;
                        let height = img.height;

                        // Downscale dimensions if larger than maxDimension
                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        
                        // Fill white background in case of transparent PNG converted to JPEG
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, width, height);
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                callback(file, file.size, file.size);
                                return;
                            }

                            // If compressed blob is smaller than original, use it
                            if (blob.size < file.size) {
                                const newName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                                const compressedFile = new File([blob], newName, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                callback(compressedFile, file.size, compressedFile.size);
                            } else {
                                callback(file, file.size, file.size);
                            }
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = function() {
                        callback(file, file.size, file.size);
                    };
                };
                reader.onerror = function() {
                    callback(file, file.size, file.size);
                };
            }

            // Image file selection, instant preview & automatic compression
            $('#np_image').on('change', function(e) {
                const file = this.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        notyf.error('Please select a valid image file.');
                        $(this).val('');
                        $('#np_image_label').text('Take photo or choose image...');
                        $('#np_preview_container').hide();
                        $('#np_compression_info').hide().empty();
                        return;
                    }
                    if (file.size > 20 * 1024 * 1024) {
                        notyf.error('Image size must be less than 20MB.');
                        $(this).val('');
                        $('#np_image_label').text('Take photo or choose image...');
                        $('#np_preview_container').hide();
                        $('#np_compression_info').hide().empty();
                        return;
                    }

                    $('#np_image_label').text(file.name);
                    $('#np_compression_info').html('<span class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i> Optimizing & compressing image...</span>').show();

                    // Show temporary preview
                    const tempUrl = URL.createObjectURL(file);
                    $('#np_preview_img').attr('src', tempUrl);
                    $('#np_preview_container').show();

                    // Compress: max 1600px width/height, 0.75 JPEG quality
                    compressImageFile(file, 1600, 0.75, function(compressedFile, origSize, newSize) {
                        // Replace the input's file with compressed version
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(compressedFile);
                            $('#np_image')[0].files = dt.files;
                        } catch (err) {
                            console.warn('DataTransfer API error, will upload original:', err);
                        }

                        const compressedUrl = URL.createObjectURL(compressedFile);
                        $('#np_preview_img').attr('src', compressedUrl);
                        $('#np_image_label').text(compressedFile.name);

                        if (origSize && newSize && newSize < origSize) {
                            const savedPct = Math.round(((origSize - newSize) / origSize) * 100);
                            $('#np_compression_info').html(
                                '<span class="badge badge-success p-1 font-weight-normal text-wrap" style="font-size: 11.5px;">' +
                                '<i class="fas fa-check-circle mr-1"></i> Compressed: <strong>' + formatBytes(newSize) + '</strong> ' +
                                '<span class="text-light">(from ' + formatBytes(origSize) + ', -' + savedPct + '%)</span>' +
                                '</span>'
                            ).show();
                        } else if (newSize) {
                            $('#np_compression_info').html(
                                '<span class="badge badge-secondary p-1 font-weight-normal" style="font-size: 11.5px;">' +
                                'Size: ' + formatBytes(newSize) +
                                '</span>'
                            ).show();
                        }
                    });
                } else {
                    $('#np_image_label').text('Take photo or choose image...');
                    $('#np_preview_container').hide();
                    $('#np_compression_info').hide().empty();
                }
            });

            // Remove selected image
            $('#np_remove_img_btn').on('click', function() {
                $('#np_image').val('');
                $('#np_image_label').text('Take photo or choose image...');
                $('#np_preview_container').hide();
                $('#np_preview_img').attr('src', '');
                $('#np_compression_info').hide().empty();
            });

            // If modal dismissed without saving, reset dropdown
            $('#noPaymentModal').on('hidden.bs.modal', function() {
                if (currentActiveNoPaymentRow && !isSubmittingNoPayment) {
                    const typeSelect = currentActiveNoPaymentRow.find('select[name="type"]');
                    if (typeSelect.val() === 'NO PAYMENT') {
                        typeSelect.val('');
                        currentActiveNoPaymentRow.find('input[name="collection"]').val('').prop('readonly', false).prop('disabled', false);
                        const savingsInput = currentActiveNoPaymentRow.find('input[name="savings"]');
                        if (savingsInput.length) {
                            savingsInput.val('').prop('readonly', false).prop('disabled', false);
                        }
                        updateBulkTotal();
                    }
                }
            });

            // Submit handler for No Payment Modal
            $('#noPaymentForm').on('submit', function(e) {
                const fileInput = $('#np_image')[0];
                if (!fileInput.files || fileInput.files.length === 0) {
                    e.preventDefault();
                    notyf.error('Proof image is required when tagging as NO PAYMENT.');
                    return false;
                }
                isSubmittingNoPayment = true;
                $('#npSubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            });

            // Intercept row save button if NO PAYMENT is selected
            $(document).on('click', '.btn-save-row', function(e) {
                const tr = $(this).closest('tr');
                const typeSelect = tr.find('select[name="type"]');
                if (typeSelect.val() === 'NO PAYMENT') {
                    e.preventDefault();
                    openNoPaymentModal(tr);
                }
            });

            $(document).on('change', 'select[name="type"]', function() {
                const tr = $(this).closest('tr');
                const val = $(this).val();
                const collectionInput = tr.find('input[name="collection"]');
                const savingsInput = tr.find('input[name="savings"]');

                if (val === 'NO PAYMENT') {
                    collectionInput.val('');
                    collectionInput.prop('readonly', true);
                    if (savingsInput.length) {
                        savingsInput.val('');
                        savingsInput.prop('readonly', true);
                    }
                    openNoPaymentModal(tr);
                } else {
                    collectionInput.prop('readonly', false);
                    if (savingsInput.length) {
                        savingsInput.prop('readonly', false);
                    }
                }
                updateBulkTotal();
            });

            // View Proof modal click handler
            $(document).on('click', '.view-proof-btn', function() {
                const clientName = $(this).data('client-name');
                const imageSrc = $(this).data('image');
                const remarks = $(this).data('remarks');

                $('#vp_client_name').text(clientName || 'N/A');

                if (imageSrc && imageSrc.trim() !== '') {
                    $('#vp_image').attr('src', imageSrc);
                    $('#vp_image_link').attr('href', imageSrc);
                    $('#vp_image_container').show();
                    $('#vp_no_image').hide();
                } else {
                    $('#vp_image_container').hide();
                    $('#vp_no_image').show();
                }

                if (remarks && remarks.trim() !== '') {
                    $('#vp_remarks_text').text(remarks);
                    $('#vp_remarks_container').show();
                    $('#vp_no_remarks').hide();
                } else {
                    $('#vp_remarks_container').hide();
                    $('#vp_no_remarks').show();
                }

                $('#viewProofModal').modal('show');
            });

            // Bulk Save functionality (without checkboxes)
            $('#bulkSaveBtn').on('click', function() {
                const rows = table.rows().nodes(); // This gets all rows across all pages
                let paymentsToSave = [];

                $(rows).each(function() {
                    const tr = $(this);
                    const typeSelect = tr.find('select[name="type"]');

                    // Only include rows where a payment type has been selected
                    if (typeSelect.length && typeSelect.val()) {
                        paymentsToSave.push({
                            tr: tr,
                            type: typeSelect.val(),
                            collection: tr.find('input[name="collection"]').val() || null,
                            savings: tr.find('input[name="savings"]').val() || null,
                            clientId: tr.data('client-id'),
                            loanId: tr.data('loan-id'),
                            areaId: tr.data('area-id'),
                            daily: tr.data('daily')
                        });
                    }
                });

                if (paymentsToSave.length === 0) {
                    notyf.error(
                        'No collections found. Please select a Payment Type for the rows you want to save.'
                    );
                    return;
                }

                // Disallow bulk saving rows with NO PAYMENT because proof image is required
                const noPaymentEntries = paymentsToSave.filter(p => p.type === 'NO PAYMENT');
                if (noPaymentEntries.length > 0) {
                    notyf.error('Clients tagged as "NO PAYMENT" require proof image and must be saved individually.');
                    return;
                }

                $('#bulkInputs').empty();
                paymentsToSave.forEach((payment, index) => {
                    $('#bulkInputs').append(`
                        <input type="hidden" name="payments[${index}][client_id]" value="${payment.clientId}">
                        <input type="hidden" name="payments[${index}][loan_id]" value="${payment.loanId}">
                        <input type="hidden" name="payments[${index}][area_id]" value="${payment.areaId}">
                        <input type="hidden" name="payments[${index}][daily]" value="${payment.daily}">
                        <input type="hidden" name="payments[${index}][collection]" value="${payment.collection}">
                        <input type="hidden" name="payments[${index}][savings]" value="${payment.savings || ''}">
                        <input type="hidden" name="payments[${index}][type]" value="${payment.type}">
                    `);
                });

                if (confirm(
                        `Are you sure you want to bulk save ${paymentsToSave.length} collections across all pages?`
                    )) {
                    $('#bulkForm').submit();
                }
            });

            // Redirect on area selection change
            $('#areaSelect').on('change', function() {
                var areaId = $(this).val();
                var url = new URL(window.location.href);
                url.searchParams.set('area_id', areaId);
                window.location.href = url.toString();
            });

        });
    </script>

    <form id="bulkForm" action="{{ route('collector.collections.bulk-store') }}" method="POST"
        style="display: none;">
        @csrf
        <input type="hidden" name="reference_no" value="{{ $refNo }}">
        <input type="hidden" name="due_date" value="{{ $selectedDate }}">
        <div id="bulkInputs"></div>
    </form>
</body>

</html>
