<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $areas_name }} - {{ ($reportMode ?? 'detailed') === 'summary' ? 'Collection Summary Report' : 'Weekly Collection Report' }} {{ ($typeFilter ?? 'all') === 'lapsed' ? '(Lapsed Accounts)' : (($typeFilter ?? 'all') === 'active' ? '(Active Clients)' : '') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 13px;
            color: #000;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .report-wrapper {
            max-width: 1050px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
        }

        .report-header {
            margin-bottom: 18px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }

        .area-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0 0 2px 0;
            color: #111;
        }

        .report-type-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #0d6efd;
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            margin-bottom: 6px;
        }

        .report-type-badge.badge-summary {
            background-color: #198754;
        }

        .date-range-subtitle {
            font-size: 12.5px;
            font-weight: 600;
            color: #444;
            margin: 0;
        }

        .weekly-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 20px;
        }

        .weekly-table th,
        .weekly-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 12px;
            line-height: 1.3;
            vertical-align: middle;
        }

        .weekly-table thead th {
            background-color: #e9ecef !important;
            font-weight: 700;
            text-align: center;
            border-bottom: 1.5px solid #000;
            color: #000;
        }

        .weekly-table thead th.client-col-header {
            text-align: left;
            width: 38%;
            font-size: 12.5px;
        }

        .client-name-cell {
            font-weight: 700;
            color: #000;
            white-space: nowrap;
        }

        .client-name-cell.text-no-payment {
            color: #c92a2a !important;
        }

        .paid-cell {
            text-align: right;
            font-weight: 600;
            color: #000;
            background-color: #ffffff !important;
            white-space: nowrap;
        }

        .no-payment-cell {
            background-color: #e57373 !important;
            color: transparent;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group !important;
        }

        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .total-row td {
            background-color: #4a90e2 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 12.5px;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }

        .total-label {
            text-align: left;
            font-weight: 800 !important;
        }

        .total-amount {
            text-align: right;
            font-weight: 800 !important;
            white-space: nowrap;
        }

        /* Summary Report Specific Styles */
        .summary-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-kpi-card {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 10px 14px;
            background-color: #fdfdfd;
        }

        .summary-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .summary-kpi-value {
            font-size: 20px;
            font-weight: 800;
            color: #111;
            line-height: 1.2;
        }

        .section-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sign-off-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
        }

        .sign-box {
            width: 45%;
        }

        .sign-line {
            border-top: 1.5px solid #000;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 11.5px;
            font-weight: 600;
            color: #333;
        }

        .toolbar {
            max-width: 1050px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 10px 15px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        @media print {
            body {
                background-color: #fff !important;
                padding: 0 !important;
            }

            .report-wrapper {
                box-shadow: none !important;
                padding: 10px 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .no-print {
                display: none !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group !important;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .weekly-table th,
            .weekly-table td {
                padding: 4px 6px !important;
                font-size: 11px !important;
            }

            .no-payment-cell {
                background-color: #e57373 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .total-row td {
                background-color: #4a90e2 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .summary-kpi-card {
                border: 1px solid #000 !important;
                padding: 6px 10px !important;
            }

            .summary-kpi-value {
                font-size: 16px !important;
            }
        }
    </style>
</head>

<body>

    <!-- Toolbar for screen view -->
    <div class="toolbar no-print">
        <a href="javascript:window.history.back()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex align-items-center gap-2">
            <form action="" method="GET" class="d-flex align-items-center gap-2 m-0">
                <input type="hidden" name="from" value="{{ $fromFormatted }}">
                <input type="hidden" name="to" value="{{ $toFormatted }}">
                
                <label class="form-label mb-0 fw-semibold text-nowrap">View:</label>
                <select name="mode" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="detailed" {{ ($reportMode ?? 'detailed') === 'detailed' ? 'selected' : '' }}>Detailed (Clients + Totals)</option>
                    <option value="summary" {{ ($reportMode ?? 'detailed') === 'summary' ? 'selected' : '' }}>Summary Only (Daily Totals)</option>
                </select>

                <label class="form-label mb-0 fw-semibold text-nowrap ms-2">Filter:</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ ($typeFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Clients</option>
                    <option value="active" {{ ($typeFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active Clients</option>
                    <option value="lapsed" {{ ($typeFilter ?? 'all') === 'lapsed' ? 'selected' : '' }}>Lapsed Account</option>
                </select>
            </form>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 ms-2">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="report-wrapper">
        <!-- Header -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="area-title">{{ $areas_name }}</h1>
                    <span class="report-type-badge {{ ($reportMode ?? 'detailed') === 'summary' ? 'badge-summary' : '' }}">
                        {{ ($reportMode ?? 'detailed') === 'summary' ? 'Daily Collection Summary Report' : 'Weekly Collection Matrix Report' }}
                    </span>
                    <div class="date-range-subtitle">
                        <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromFormatted)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($toFormatted)->format('M d, Y') }}
                        <span class="mx-1">&bull;</span>
                        <strong>Filter:</strong>
                        @if (($typeFilter ?? 'all') === 'active')
                            <span class="text-success">Active Clients</span>
                        @elseif (($typeFilter ?? 'all') === 'lapsed')
                            <span class="text-danger">Lapsed Account</span>
                        @else
                            <span>All Clients</span>
                        @endif
                    </div>
                </div>
                <div class="text-end text-muted small d-none d-sm-block">
                    <div><strong>Printed:</strong> {{ \Carbon\Carbon::now('Asia/Manila')->format('m/d/Y h:i A') }}</div>
                    <div><strong>Location:</strong> {{ $location_name }}</div>
                </div>
            </div>
        </div>

        @if (($reportMode ?? 'detailed') === 'summary')
            {{-- ========================================================= --}}
            {{-- SUMMARY REPORT VIEW (Daily Totals Only as requested)     --}}
            {{-- ========================================================= --}}

            <!-- Summary KPI Cards -->
            <div class="summary-kpi-grid">
                <div class="summary-kpi-card">
                    <div class="summary-kpi-label"><i class="fas fa-coins text-warning me-1"></i> Grand Total Collection</div>
                    <div class="summary-kpi-value text-primary">₱ {{ number_format($grandTotal, 2) }}</div>
                </div>
                <div class="summary-kpi-card">
                    <div class="summary-kpi-label"><i class="fas fa-calendar-day text-info me-1"></i> Days with Collection</div>
                    <div class="summary-kpi-value">{{ count($dates) }} Day{{ count($dates) === 1 ? '' : 's' }}</div>
                </div>
                <div class="summary-kpi-card">
                    <div class="summary-kpi-label"><i class="fas fa-calculator text-success me-1"></i> Average Daily Collection</div>
                    <div class="summary-kpi-value">
                        ₱ {{ count($dates) > 0 ? number_format($grandTotal / count($dates), 2) : '0.00' }}
                    </div>
                </div>
                <div class="summary-kpi-card">
                    <div class="summary-kpi-label"><i class="fas fa-users text-secondary me-1"></i> Total Paying Clients</div>
                    <div class="summary-kpi-value">{{ $totalPayingClients ?? 0 }}</div>
                </div>
            </div>

            <!-- 1. Horizontal Matrix Total (Matches the exact circled footer in the screenshot) -->
            <div class="section-title">
                <i class="fas fa-table text-primary"></i> Daily Collection Matrix Summary
            </div>
            <table class="weekly-table">
                <thead>
                    <tr>
                        <th class="client-col-header">SUMMARY ROW</th>
                        @foreach ($dates as $date)
                            <th>{{ \Carbon\Carbon::parse($date)->format('j-M (D)') }}</th>
                        @endforeach
                        <th>GRAND TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="total-row">
                        <td class="total-label">TOTAL COLLECTION:</td>
                        @foreach ($dates as $date)
                            <td class="total-amount">
                                ₱ {{ number_format($dailyTotals[$date] ?? 0, 2) }}
                            </td>
                        @endforeach
                        <td class="total-amount" style="background-color: #2b6cb0 !important; color: #fff !important;">
                            ₱ {{ number_format($grandTotal, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- 2. Itemized Daily Ledger Breakdown Table -->
            <div class="section-title mt-4">
                <i class="fas fa-list-ol text-primary"></i> Itemized Daily Breakdown
            </div>
            <table class="weekly-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="text-align: left;">DATE</th>
                        <th style="text-align: left;">DAY</th>
                        <th style="text-align: center;">PAYING CLIENTS</th>
                        <th style="text-align: right;">TOTAL COLLECTION</th>
                        <th style="text-align: right; width: 110px;">% SHARE</th>
                    </tr>
                </thead>
                <tbody>
                    @php $dayIndex = 1; @endphp
                    @forelse ($dates as $date)
                        @php
                            $dayTotal = $dailyTotals[$date] ?? 0;
                            $pct = $grandTotal > 0 ? ($dayTotal / $grandTotal) * 100 : 0;
                            $payingCount = $dailyPayingCounts[$date] ?? 0;
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold">{{ $dayIndex++ }}</td>
                            <td class="fw-bold">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($date)->format('l') }}</td>
                            <td class="text-center">{{ $payingCount }} client{{ $payingCount === 1 ? '' : 's' }}</td>
                            <td class="text-end fw-bold {{ $dayTotal > 0 ? 'text-success' : 'text-muted' }}">
                                ₱ {{ number_format($dayTotal, 2) }}
                            </td>
                            <td class="text-end text-muted">{{ number_format($pct, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">
                                No collection records found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="total-label text-uppercase">GRAND TOTAL</td>
                        <td class="text-center font-weight-bold">{{ $totalPayingClients ?? 0 }} Clients</td>
                        <td class="total-amount">₱ {{ number_format($grandTotal, 2) }}</td>
                        <td class="text-end font-weight-bold">100.0%</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Sign Off -->
            <div class="sign-off-section">
                <div class="sign-box">
                    <div class="sign-line">Prepared By (Collector / Secretary)</div>
                </div>
                <div class="sign-box text-end">
                    <div class="sign-line" style="margin-left: auto; width: 85%;">Checked / Verified By (Admin / Manager)</div>
                </div>
            </div>

        @else
            {{-- ========================================================= --}}
            {{-- DETAILED REPORT VIEW (With all client rows + footer total) --}}
            {{-- ========================================================= --}}
            <table class="weekly-table">
                <thead>
                    <tr>
                        <th class="client-col-header">CLIENT NAME</th>
                        @foreach ($dates as $date)
                            <th>{{ \Carbon\Carbon::parse($date)->format('j-M') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportRows as $row)
                        <tr>
                            <td class="client-name-cell {{ $row->has_zero_payment ? 'text-no-payment' : '' }}">
                                {{ $row->fullname }}
                            </td>
                            @foreach ($dates as $date)
                                @php
                                    $col = $row->payments[$date] ?? null;
                                @endphp
                                @if ($col !== null && $col > 0)
                                    <td class="paid-cell">{{ number_format($col, 2) }}</td>
                                @else
                                    <td class="no-payment-cell">&nbsp;</td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($dates) + 1 }}" class="text-center py-3 text-muted">
                                No {{ ($typeFilter ?? 'all') === 'lapsed' ? 'lapsed account' : (($typeFilter ?? 'all') === 'active' ? 'active clients' : 'clients') }} found for this area.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td class="total-label">TOTAL:</td>
                        @foreach ($dates as $date)
                            <td class="total-amount">{{ number_format($dailyTotals[$date] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

</body>

</html>
