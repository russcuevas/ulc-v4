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

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #FF5F00 !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            color: #FF5F00 !important;
            background-color: #fff !important;
        }

        .nav-tabs .nav-link {
            border: none !important;
            background-color: transparent !important;
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
                            <h1 class="m-0 section-title">
                                Management Dashboard
                                @if ($selectedArea && str_contains($selectedArea, '|'))
                                    <span class="badge badge-warning"
                                        style="background-color: #FF5F00; color: white; font-size: 16px; font-weight: 600; vertical-align: middle;">
                                        {{ explode('|', $selectedArea)[1] }}
                                    </span>
                                @endif
                            </h1>
                            <p class="mb-0 range-text">
                                @if ($isFiltered)
                                    From {{ $displayFrom }} to {{ $displayTo }}
                                @else
                                    All Time Overview
                                @endif
                                @if ($selectedArea && str_contains($selectedArea, '|'))
                                    | Area: {{ str_replace('|', ' - ', $selectedArea) }}
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
                    <form action="{{ route('management.dashboard.page') }}" method="GET" class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label><i class="fas fa-calendar-alt mr-1"></i> From</label>
                            <input type="date" name="from" class="form-control" value="{{ $displayFrom }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label><i class="fas fa-calendar-alt mr-1"></i> To</label>
                            <input type="date" name="to" class="form-control" value="{{ $displayTo }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label><i class="fas fa-map-marker-alt mr-1"></i> Area</label>
                            <select name="area" class="form-control">
                                <option value="">-- All Areas --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->location_name }}|{{ $a->areas_name }}"
                                        {{ $selectedArea === $a->location_name . '|' . $a->areas_name ? 'selected' : '' }}>
                                        {{ $a->location_name }} - [{{ $a->areas_name }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary btn-block mr-2"><i
                                    class="fas fa-filter mr-1"></i> Apply Filter</button>
                            <a href="{{ route('management.dashboard.page') }}"
                                class="btn btn-outline-secondary btn-block mt-0"><i class="fas fa-undo mr-1"></i>
                                Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <div class="row">
                        <!-- Total Loan Amount Card -->
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="dashboard-card p-3 d-flex align-items-center"
                                style="border-top-color: #FF5F00; min-height: 110px;">
                                <div class="bg-light p-3 rounded-circle mr-3"
                                    style="color: #FF5F00; font-size: 24px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="label"
                                        style="font-size: 11px; text-transform: uppercase; color: #888;">Total Loan
                                        Amount</div>
                                    <div class="value" style="font-size: 22px; font-weight: 700; color: #FF5F00;">
                                        P{{ number_format($overall['total_loans_amount'], 2) }}</div>
                                    <div class="sub" style="font-size: 11px; color: #888;">
                                        {{ number_format($overall['total_loans']) }} loans in range</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Collected Card -->
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="dashboard-card p-3 d-flex align-items-center"
                                style="border-top-color: #28a745; min-height: 110px;">
                                <div class="bg-light p-3 rounded-circle mr-3"
                                    style="color: #28a745; font-size: 24px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="label"
                                        style="font-size: 11px; text-transform: uppercase; color: #888;">Total Collected
                                    </div>
                                    <div class="value" style="font-size: 22px; font-weight: 700; color: #28a745;">
                                        P{{ number_format($overall['total_collected'], 2) }}</div>
                                    <div class="sub" style="font-size: 11px; color: #888;">From daily collections
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collectibles Card -->
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="dashboard-card p-3 d-flex align-items-center"
                                style="border-top-color: #007bff; min-height: 110px;">
                                <div class="bg-light p-3 rounded-circle mr-3"
                                    style="color: #007bff; font-size: 24px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="label"
                                        style="font-size: 11px; text-transform: uppercase; color: #888;">Collectibles
                                    </div>
                                    <div class="value" style="font-size: 22px; font-weight: 700; color: #007bff;">
                                        P{{ number_format($overall['total_collectibles'], 2) }}</div>
                                    <div class="sub" style="font-size: 11px; color: #888;">Expected for selected
                                        range</div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-primary card-outline"
                                style="border-top: 4px solid #FF5F00; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); border-radius: 8px;">
                                <div class="card-header"
                                    style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                                    <h3 class="card-title font-weight-bold"
                                        style="color: #495057; margin-bottom: 0; padding: 5px 0;">
                                        <i class="fas fa-map-marked-alt mr-2 text-primary"></i>Collection per Areas
                                    </h3>
                                </div>
                                <div class="card-body">

                                    <!-- Section 1: Locations List -->
                                    <div id="locations-section">
                                        <h5 class="mb-3 text-dark font-weight-bold" style="font-size: 16px;">
                                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>Select
                                            Location
                                        </h5>
                                        <div class="table-responsive">
                                            <table id="locationSummaryTable"
                                                class="table table-striped table-hover table-bordered w-100">
                                                <thead>
                                                    <tr style="background-color: #f8f9fa;">
                                                        <th>Location</th>
                                                        <th>Total Loan Amount</th>
                                                        <th>Collectibles</th>
                                                        <th>Collected</th>
                                                        <th>Collection Rate</th>
                                                        <th class="text-center" style="width: 150px;">Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($locationSummaries as $loc)
                                                        @php
                                                            $rate =
                                                                $loc->total_collectibles > 0
                                                                    ? ($loc->total_collected /
                                                                            $loc->total_collectibles) *
                                                                        100
                                                                    : 0;
                                                            $progressClass =
                                                                $rate >= 90
                                                                    ? 'bg-success'
                                                                    : ($rate >= 50
                                                                        ? 'bg-warning'
                                                                        : 'bg-danger');
                                                        @endphp
                                                        <tr>
                                                            <td class="font-weight-bold"><i
                                                                    class="fas fa-city mr-2 text-secondary"></i>{{ $loc->location_name }}
                                                            </td>
                                                            <td>₱{{ number_format($loc->total_loans_amount, 2) }}
                                                            </td>
                                                            <td>₱{{ number_format($loc->total_collectibles, 2) }}
                                                            </td>
                                                            <td class="text-success font-weight-bold">
                                                                ₱{{ number_format($loc->total_collected, 2) }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="mr-2 font-weight-bold"
                                                                        style="min-width: 45px; font-size: 13px;">{{ number_format($rate, 1) }}%</span>
                                                                    <div class="progress"
                                                                        style="height: 6px; flex-grow: 1;">
                                                                        <div class="progress-bar {{ $progressClass }}"
                                                                            role="progressbar"
                                                                            style="width: {{ min(100, $rate) }}%"
                                                                            aria-valuenow="{{ $rate }}"
                                                                            aria-valuemin="0" aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    class="btn btn-xs btn-primary font-weight-bold px-3 py-1 view-areas-btn"
                                                                    data-location="{{ $loc->location_name }}"
                                                                    style="background-color: #FF5F00; border-color: #FF5F00; border-radius: 4px;">
                                                                    <i class="fas fa-folder-open mr-1"></i>
                                                                    View Areas
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">
                                                                No location data found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Section 2: Areas Details (Hidden by default) -->
                                    <div id="areas-section" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="m-0 text-dark font-weight-bold" style="font-size: 16px;">
                                                <i class="fas fa-map-marked-alt text-primary mr-2"></i>Areas
                                                under <span id="selected-location-label"
                                                    class="text-primary font-weight-bold"></span>
                                            </h5>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary font-weight-bold back-to-locations-btn"
                                                style="border-radius: 4px;">
                                                <i class="fas fa-arrow-left mr-1"></i> Back to Locations
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="areaSummaryTable"
                                                class="table table-striped table-hover table-bordered w-100">
                                                <thead>
                                                    <tr style="background-color: #f8f9fa;">
                                                        <th style="display: none;">Location</th>
                                                        <th>Area Name</th>
                                                        <th>Total Loan Amount</th>
                                                        <th>Collectibles</th>
                                                        <th>Collected</th>
                                                        <th>Collection Rate</th>
                                                        <th class="text-center" style="width: 150px;">Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($areaSummaries as $area)
                                                        @php
                                                            $rate =
                                                                $area->total_collectibles > 0
                                                                    ? ($area->total_collected /
                                                                            $area->total_collectibles) *
                                                                        100
                                                                    : 0;
                                                            $progressClass =
                                                                $rate >= 90
                                                                    ? 'bg-success'
                                                                    : ($rate >= 50
                                                                        ? 'bg-warning'
                                                                        : 'bg-danger');
                                                        @endphp
                                                        <tr>
                                                            <td class="loc-name" style="display: none;">
                                                                {{ $area->location_name }}</td>
                                                            <td class="font-weight-bold">
                                                                {{ $area->areas_name }}</td>
                                                            <td>₱{{ number_format($area->total_loans_amount, 2) }}
                                                            </td>
                                                            <td>₱{{ number_format($area->total_collectibles, 2) }}
                                                            </td>
                                                            <td class="text-success font-weight-bold">
                                                                ₱{{ number_format($area->total_collected, 2) }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="mr-2 font-weight-bold"
                                                                        style="min-width: 45px; font-size: 13px;">{{ number_format($rate, 1) }}%</span>
                                                                    <div class="progress"
                                                                        style="height: 6px; flex-grow: 1;">
                                                                        <div class="progress-bar {{ $progressClass }}"
                                                                            role="progressbar"
                                                                            style="width: {{ min(100, $rate) }}%"
                                                                            aria-valuenow="{{ $rate }}"
                                                                            aria-valuemin="0" aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('management.areas.collections.references', $area->id) }}"
                                                                    class="btn btn-xs btn-primary font-weight-bold px-3 py-1"
                                                                    style="background-color: #FF5F00; border-color: #FF5F00; border-radius: 4px;">
                                                                    View per day
                                                                    collections
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">
                                                                No areas data found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

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
            // View Areas click handler
            $(document).on('click', '.view-areas-btn', function() {
                let locationName = $(this).data('location');

                // Set the label
                $('#selected-location-label').text(locationName);

                // Filter the area table rows manually
                $('#areaSummaryTable tbody tr').each(function() {
                    let rowLoc = $(this).find('.loc-name').text().trim();
                    if (rowLoc === locationName) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // Switch sections with a transition
                $('#locations-section').fadeOut(200, function() {
                    $('#areas-section').fadeIn(200);
                });
            });

            // Back to Locations click handler
            $(document).on('click', '.back-to-locations-btn', function() {
                // Switch sections with a transition
                $('#areas-section').fadeOut(200, function() {
                    $('#locations-section').fadeIn(200);
                });
            });
        });
    </script>
</body>

</html>
