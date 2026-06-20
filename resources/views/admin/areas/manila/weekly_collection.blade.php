@php
    $todayOfWeek = now()->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    $allowedDay = null;
    $allowedDayName = '';

    if (stripos($location_name, 'Financial Counselor') !== false) {
        $allowedDay = 6; // Saturday
        $allowedDayName = 'Saturday';
    } elseif (stripos($location_name, 'Caloocan') !== false) {
        $allowedDay = 1; // Monday
        $allowedDayName = 'Monday';
    } elseif (stripos($location_name, 'Manila') !== false) {
        $allowedDay = 2; // Tuesday
        $allowedDayName = 'Tuesday';
    }

    $isAllowedToday = ($allowedDay === null || $todayOfWeek === $allowedDay);
@endphp
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

        .breadcrumb-item a {
            color: #FF5F00 !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb-item a:hover {
            color: #cc4c00 !important;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: #ffa366;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .range-card {
            border-left: 4px solid #FF5F00;
            background-color: #fcfcfc;
        }
    </style>
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-day.selected, 
        .flatpickr-day.selected:hover, 
        .flatpickr-day.selected:focus {
            background: #FF5F00 !important;
            border-color: #FF5F00 !important;
        }
        .flatpickr-months .flatpickr-month {
            color: #FF5F00 !important;
            fill: #FF5F00 !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowUp:after {
            border-bottom-color: #FF5F00 !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown:after {
            border-top-color: #FF5F00 !important;
        }
        .flatpickr-day.today {
            border-color: #ffa366 !important;
        }
        .flatpickr-day.today:hover {
            background: #ffa366 !important;
            color: white !important;
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
                            <h1 class="m-0">Weekly Collection</h1>
                            <ol class="breadcrumb mt-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard.page') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.areas.page') }}">Areas</a>
                                </li>
                                <li class="breadcrumb-item active">Weekly Collection -
                                    {{ str_ends_with($location_name, 'Area') ? $location_name : $location_name . ' Area' }}
                                </li>
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
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-calendar-alt text-orange mr-1"></i> Weekly Collection -
                                {{ str_ends_with($location_name, 'Area') ? $location_name : $location_name . ' Area' }}
                            </h3>
                        </div>

                        <div class="card-body">
                            @if(!$isAllowedToday)
                                <div class="alert alert-warning border-0 shadow-sm mb-4">
                                    <h5 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-2"></i> Action Restricted</h5>
                                    Weekly collection for <strong>{{ $location_name }}</strong> is scheduled only on <strong>{{ $allowedDayName }}s</strong> (12:00 AM to 11:59 PM). 
                                    You cannot collect payments today.
                                </div>
                            @endif

                            <!-- Date Selector Controls -->
                            <div class="row align-items-end g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="weeklyDate" class="font-weight-600">Select Date:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            </div>
                                            <input type="text" id="weeklyDate" class="form-control bg-white"
                                                placeholder="Select start date" readonly {{ !$isAllowedToday ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-600">Selected Range (5 Days):</label>
                                        <div id="dateRangeDisplay"
                                            class="p-2 border rounded font-weight-bold text-center range-card"
                                            style="min-height: 38px; line-height: 22px;">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success w-100 font-weight-bold"
                                        id="sendWeeklyBtn" disabled>
                                        <i class="fas fa-paper-plane mr-1"></i> Collect Weekly Payment
                                    </button>
                                </div>
                            </div>

                            <hr>

                            <!-- Collectors List Table -->
                            <h5 class="mt-4 mb-3 font-weight-bold text-dark">
                                <i class="fas fa-map-marked-alt text-orange mr-1"></i> Assigned Areas List
                            </h5>
                            <div class="table-responsive">
                                <table id="areasTable" class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Location</th>
                                            <th>Area Name / Code</th>
                                            <th>Assigned Field Collector (FC)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($locationAreas as $sArea)
                                            <tr>
                                                <td>{{ $sArea->location_name }}</td>
                                                <td class="font-weight-500">{{ $sArea->areas_name }}</td>
                                                <td>{{ $sArea->collector_name ?? 'Unassigned' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    <i class="fas fa-info-circle fa-2x mb-2 text-gray"></i><br>
                                                    No assigned areas found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            @if(!$isAllowedToday)
                // Schedule restriction: do not initialize flatpickr
                $('#weeklyDate').prop('disabled', true);
                $('#sendWeeklyBtn').prop('disabled', true);
            @else
                flatpickr("#weeklyDate", {
                disable: [
                    function(date) {
                        // Disable all days except Mondays (1)
                        return date.getDay() !== 1;
                    }
                ],
                locale: {
                    firstDayOfWeek: 1 // Start week on Monday
                },
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        let startDate = selectedDates[0];
                        let endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 4);

                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        };
                        let startStr = startDate.toLocaleDateString('en-US', options);
                        let endStr = endDate.toLocaleDateString('en-US', options);

                        $('#dateRangeDisplay').html(
                            `FROM <strong class="text-orange">${startStr}</strong> TO <strong class="text-orange">${endStr}</strong>`
                        );
                        $('#sendWeeklyBtn').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('admin.areas.collections.weekly_check', ['location' => $location_name]) }}",
                            method: "GET",
                            data: { date: dateStr },
                            success: function(response) {
                                if (response.exists) {
                                    $('#dateRangeDisplay').html(
                                        `FROM <strong class="text-orange">${startStr}</strong> TO <strong class="text-orange">${endStr}</strong><br>` +
                                        `<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> ALREADY COLLECTED FOR THIS PERIOD</span>`
                                    );
                                    $('#sendWeeklyBtn').prop('disabled', true);
                                } else {
                                    $('#sendWeeklyBtn').prop('disabled', false);
                                }
                            },
                            error: function() {
                                $('#sendWeeklyBtn').prop('disabled', false);
                            }
                        });
                    } else {
                        $('#dateRangeDisplay').html('FROM ... TO ...');
                        $('#sendWeeklyBtn').prop('disabled', true);
                    }
                }
            });
            @endif

            $('#sendWeeklyBtn').on('click', function() {
                let dateVal = $('#weeklyDate').val();
                if (!dateVal) return;

                let startDate = new Date(dateVal);
                let endDate = new Date(dateVal);
                endDate.setDate(startDate.getDate() + 4);

                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                let startStr = startDate.toLocaleDateString('en-US', options);
                let endStr = endDate.toLocaleDateString('en-US', options);

                Swal.fire({
                    title: 'Collect Weekly Payment?',
                    html: `Are you sure you want to process and collect weekly payments for the period:<br><strong>${startStr} - ${endStr}</strong>?<br><br><strong>Areas:</strong> {{ implode(', ', $locationAreas->pluck('areas_name')->toArray()) }}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Collect Now',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Please wait while we process the weekly collection.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('admin.areas.collections.weekly_collect', ['location' => $location_name]) }}",
                            method: "POST",
                            data: {
                                date: dateVal,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Collected!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                let errorMsg =
                                    'Something went wrong while processing the weekly collections.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Error!',
                                    errorMsg,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
