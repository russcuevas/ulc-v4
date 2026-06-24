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
                            <h1 class="m-0">{{ $location_name }} - [{{ $areas_name }}]</h1>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <i class="fas fa-user"></i> Client Information
                                </div>

                                <div class="card-body">
                                    <p><strong>Full Name:</strong> {{ $client->fullname }}</p>
                                    <p><strong>Phone:</strong> {{ $client->phone }}</p>
                                    <p><strong>Gender:</strong> {{ $client->gender }}</p>
                                    <p><strong>Phone 2:</strong> {{ $client->phone_number_2 }}</p>
                                    <p>
                                        <strong>Date Approved:</strong>
                                        {{ \Carbon\Carbon::parse($client->created_at)->format('F d, Y') }}
                                        <br>
                                        <span class="badge bg-primary">
                                            No of Loans: {{ count($loans) }}
                                        </span>
                                    </p>

                                    @php
                                         $latestLoan = $loans->sortByDesc('created_at')->first();
                                         $canRenew =
                                             $latestLoan &&
                                             $latestLoan->balance == 0 &&
                                             strtolower($latestLoan->status) === 'paid';
                                     @endphp

                                    <button class="btn btn-sm btn-warning mb-2" data-toggle="modal"
                                        data-target="#editClientModal">
                                        <i class="fas fa-edit"></i> Edit information
                                    </button>

                                    <br>

                                    @if ($canRenew)
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                            data-target="#renewLoanModal">
                                            <i class="fas fa-redo"></i> Renew Loan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- renew modal --}}
                        @include('secretary.areas.modals.renew_loan')

                        {{-- edit information modal --}}
                        @include('secretary.areas.modals.edit_information')

                        <!-- RIGHT: LOAN TABLE -->
                        <div class="col-md-9">
                            <div class="card card-primary card-outline">
                                    <div class="card-header">
                                                                    <div class="d-flex justify-content-between align-items-center w-100">

                                        <span>
                                            <i class="fas fa-file-invoice-dollar"></i> Loan History
                                        </span>
                                        <a href="{{ route('secretary.area.clients.print_summary_loan', $client->id) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-print"></i> Print Summary Loan
                                        </a>
                                                                    </div>
                                    </div>
                                    

                                <div class="card-body table-responsive">
                                    <table id="loanTable" class="table table-bordered table-striped">
                                        <thead class="table-light" style="font-size: 12px;">
                                            <tr>
                                                <th>PN #</th>
                                                <th>Release #</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Mode</th>
                                                <th>Amount</th>
                                                <th>Balance</th>
                                                @if (stripos($location_name, 'Financial Counselor') !== false)
                                                    <th>Savings</th>
                                                @endif
                                                <th>Daily</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody style="font-size: 12px;">
                                            @forelse ($loans as $loan)
                                                <tr class="">

                                                    <td>{{ $loan->pn_number }}</td>
                                                    <td>{{ $loan->release_number }}</td>

                                                    <td>{{ \Carbon\Carbon::parse($loan->loan_from)->format('M d, Y') }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}
                                                    </td>

                                                    <td>
                                                        <span
                                                            class="badge {{ $loan->loan_status === 'new' ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ ucfirst($loan->loan_status) }}
                                                        </span>
                                                    </td>

                                                    <td>₱{{ number_format($loan->loan_amount, 2) }}</td>
                                                    <td>₱{{ number_format($loan->balance, 2) }}</td>
                                                     @if (stripos($location_name, 'Financial Counselor') !== false)
                                                         <td>₱{{ number_format($loan->savings_balance, 2) }}</td>
                                                     @endif
                                                     <td>₱{{ number_format($loan->daily, 2) }}</td>

                                                    <td>
                                                        <span
                                                            class="badge {{ $loan->status === 'paid' ? 'bg-success' : 'bg-danger' }}">
                                                            {{ ucfirst($loan->status) }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <a href="{{ route('secretary.area.clients.generate.soa', $loan->id) }}"
                                                            target="_blank" class="btn btn-sm btn-primary">
                                                            Generate SOA
                                                        </a>
                                                    </td>

                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ stripos($location_name, 'Financial Counselor') !== false ? 11 : 10 }}" class="text-center">No loans found.</td>
                                                </tr>
                                            @endforelse
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

                var loaderHtml = '<div id="' + loaderId + '" class="text-center py-3 lazy-loader-indicator" style="display: none;">' +
                    '<div class="spinner-border text-primary" role="status" style="color: #FF5F00 !important;">' +
                    '<span class="sr-only">Loading...</span>' +
                    '</div>' +
                    '</div>';
                $(tableId).after(loaderHtml);

                if ($('#lazy-hidden-style').length === 0) {
                    $('head').append('<style id="lazy-hidden-style">.lazy-hidden { display: none !important; }</style>');
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
            }

            setupLazyLoad('#loanTable');
        });
    </script>
</body>

</html>
