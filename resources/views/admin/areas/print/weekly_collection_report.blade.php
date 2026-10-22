<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $areas_name }} - {{ ($reportMode ?? 'detailed') === 'summary' ? 'Collection Summary Report' : 'Weekly Collection Report' }} {{ !empty($selectedClientName) ? '('.$selectedClientName.')' : (!empty($selectedClientsCount) ? '('.$selectedClientsCount.' Selected Clients)' : (($typeFilter ?? 'all') === 'lapsed' ? '(Lapsed Accounts)' : (($typeFilter ?? 'all') === 'active' ? '(Active Clients)' : ''))) }}</title>

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
            background-color: #fff4f4 !important;
            color: #212529 !important;
            text-align: center;
            vertical-align: middle;
            padding: 6px 4px !important;
            min-width: 100px;
        }

        .no-payment-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }

        .no-payment-thumb {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1.5px solid #dc3545;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            display: inline-block;
        }

        .no-payment-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.35);
        }

        .no-payment-badge-no-img {
            font-size: 9.5px;
            font-weight: 600;
            color: #b02a37;
            background: #ffe3e3;
            border: 1px dashed #f5c2c7;
            border-radius: 3px;
            padding: 1px 4px;
            line-height: 1.2;
            display: inline-block;
            white-space: nowrap;
        }

        .no-payment-remarks {
            font-size: 10px;
            color: #111;
            line-height: 1.25;
            word-break: break-word;
            max-width: 120px;
            text-align: center;
        }

        .no-payment-remarks .remarks-tag {
            font-weight: 700;
            color: #b02a37;
        }

        .no-payment-badge-no-remarks {
            font-size: 9.5px;
            color: #6c757d;
            font-style: italic;
            line-height: 1.2;
            white-space: nowrap;
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

        /* Proof Viewer Floating Navigation Arrows */
        .proof-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #ced4da;
            color: #1e293b;
            font-size: 19px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1065;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.22);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            user-select: none;
        }

        .proof-nav-prev {
            left: -24px;
        }

        .proof-nav-next {
            right: -24px;
        }

        .proof-nav-arrow:hover:not(:disabled) {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
            transform: translateY(-50%) scale(1.12);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.45);
        }

        .proof-nav-arrow:active:not(:disabled) {
            transform: translateY(-50%) scale(0.95);
        }

        .proof-nav-arrow:disabled {
            opacity: 0.22;
            cursor: not-allowed;
            background: #e9ecef;
            color: #adb5bd;
            border-color: #dee2e6;
            box-shadow: none;
            pointer-events: none;
        }

        @media (max-width: 580px) {
            .proof-nav-prev {
                left: 10px;
            }
            .proof-nav-next {
                right: 10px;
            }
            .proof-nav-arrow {
                width: 42px;
                height: 42px;
                font-size: 16px;
                background: rgba(255, 255, 255, 0.94);
            }
        }

        .proof-main-image {
            transition: opacity 0.15s ease-in-out;
        }

        .proof-main-image:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
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
                background-color: #fff4f4 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 3px 2px !important;
            }

            .no-payment-thumb {
                width: 32px !important;
                height: 32px !important;
                border: 1px solid #999 !important;
                box-shadow: none !important;
            }

            .no-payment-badge-no-img {
                font-size: 8px !important;
                border: 0.5px solid #ccc !important;
                background: none !important;
                color: #555 !important;
                padding: 0 2px !important;
            }

            .no-payment-remarks {
                font-size: 8.5px !important;
                max-width: 100% !important;
            }

            .no-payment-badge-no-remarks {
                font-size: 8px !important;
                color: #666 !important;
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
                @if (!empty($selectedClientId))
                    <input type="hidden" name="client_id" value="{{ $selectedClientId }}">
                @endif
                @if (!empty($selectedClientIds))
                    @if (is_array($selectedClientIds))
                        @foreach ($selectedClientIds as $cid)
                            <input type="hidden" name="client_ids[]" value="{{ $cid }}">
                        @endforeach
                    @else
                        <input type="hidden" name="client_ids" value="{{ $selectedClientIds }}">
                    @endif
                @endif
                
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
                        @if (!empty($selectedClientName))
                            <span class="badge bg-primary text-white" style="font-size: 11px;">Client: {{ $selectedClientName }}</span>
                        @elseif (!empty($selectedClientsCount))
                            <span class="badge bg-primary text-white" style="font-size: 11px;">{{ $selectedClientsCount }} Selected Clients</span>
                        @elseif (($typeFilter ?? 'all') === 'active')
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
            @php
                $proofItems = [];
            @endphp
            <table class="weekly-table">
                <thead>
                    <tr>
                        <th class="client-col-header">CLIENT NAME</th>
                        @foreach ($dates as $date)
                            <th style="min-width: 100px;">{{ \Carbon\Carbon::parse($date)->format('j-M') }}</th>
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
                                    $pDetail = $row->payment_details[$date] ?? null;
                                @endphp
                                @if ($col !== null && $col > 0)
                                    <td class="paid-cell">{{ number_format($col, 2) }}</td>
                                @else
                                    @php
                                        $hasImage = !empty($pDetail['image']);
                                        $hasRemarks = !empty($pDetail['remarks']);
                                        $imageSrc = $hasImage ? asset(ltrim($pDetail['image'], '/')) : '';
                                        $remarksText = $hasRemarks ? $pDetail['remarks'] : '';
                                        $formattedDate = \Carbon\Carbon::parse($date)->format('F d, Y');
                                        $currentProofIdx = null;
                                        if ($hasImage) {
                                            $currentProofIdx = count($proofItems);
                                            $proofItems[] = [
                                                'index' => $currentProofIdx,
                                                'image' => $imageSrc,
                                                'client' => $row->fullname,
                                                'date' => $formattedDate,
                                                'remarks' => $remarksText,
                                            ];
                                        }
                                    @endphp
                                    <td class="no-payment-cell">
                                        <div class="no-payment-content">
                                            @if ($hasImage)
                                                <div class="no-payment-img-wrap mb-1">
                                                    <img src="{{ $imageSrc }}" 
                                                         alt="Proof" 
                                                         class="no-payment-thumb"
                                                         title="Click to view full image (Proof #{{ $currentProofIdx + 1 }})"
                                                         onclick="openReportProofModal({{ $currentProofIdx }})"
                                                    >
                                                </div>
                                            @else
                                                <div class="no-payment-badge-no-img">No image provided</div>
                                            @endif

                                            @if ($hasRemarks)
                                                <div class="no-payment-remarks" title="{{ $remarksText }}">
                                                    <span class="remarks-tag">Remarks:</span> {{ $remarksText }}
                                                </div>
                                            @else
                                                <div class="no-payment-badge-no-remarks">No remarks provided</div>
                                            @endif
                                        </div>
                                    </td>
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

    <!-- View Proof Modal with Left & Right (< >) Navigation -->
    <div class="modal fade no-print" id="reportProofModal" tabindex="-1" aria-labelledby="reportProofModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered position-relative" style="max-width: 530px;">
            
            <!-- Left Floating Arrow Button (<) -->
            <button type="button" 
                    class="proof-nav-arrow proof-nav-prev" 
                    id="modalProofFloatPrev" 
                    onclick="navigateReportProof(-1)" 
                    title="Previous Client Proof (Left Arrow key)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <!-- Right Floating Arrow Button (>) -->
            <button type="button" 
                    class="proof-nav-arrow proof-nav-next" 
                    id="modalProofFloatNext" 
                    onclick="navigateReportProof(1)" 
                    title="Next Client Proof (Right Arrow key)">
                <i class="fas fa-chevron-right"></i>
            </button>

            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-camera text-danger me-1"></i>
                        <h6 class="modal-title fw-bold mb-0" id="reportProofModalLabel">
                            No Payment Proof & Remarks
                        </h6>
                        <span class="badge bg-danger rounded-pill px-2 py-1 ms-1" id="modalProofHeaderBadge" style="font-size: 11px;">
                            Proof 1 of 1
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm me-1">
                            <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="headerProofPrevBtn" onclick="navigateReportProof(-1)" title="Previous (&larr;)">
                                <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                            </button>
                            <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="headerProofNextBtn" onclick="navigateReportProof(1)" title="Next (&rarr;)">
                                <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-3">
                    <!-- Client Selector / Jumper Dropdown -->
                    <div class="mb-2" id="modalProofSelectWrapper" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="small text-muted mb-0 fw-bold">
                                <i class="fas fa-users text-primary me-1"></i> Jump to Client:
                            </label>
                            <span class="small text-muted" style="font-size: 11px;">(or use &larr; &rarr; keys)</span>
                        </div>
                        <select class="form-select form-select-sm fw-semibold" id="modalProofSelect" onchange="openReportProofModal(parseInt(this.value))">
                        </select>
                    </div>

                    <!-- Client and Date Info Card -->
                    <div class="mb-3 p-2 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Client:</span>
                            <strong class="text-dark" id="modalProofClient">-</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted small">Date:</span>
                            <span class="text-dark fw-semibold" id="modalProofDate">-</span>
                        </div>
                    </div>

                    <!-- Proof Image -->
                    <div class="mb-3 text-center">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="fw-bold small text-dark mb-0">
                                <i class="fas fa-image text-danger me-1"></i> Uploaded Proof Image:
                            </label>
                            <small class="text-muted" style="font-size: 11px;">Click photo to zoom</small>
                        </div>
                        <div class="bg-light p-2 rounded border">
                            <img id="modalProofImg" 
                                 src="" 
                                 alt="Proof Image" 
                                 class="img-fluid rounded proof-main-image" 
                                 style="max-height: 380px; width: auto; object-fit: contain; cursor: zoom-in;"
                                 onclick="window.open(this.src, '_blank')"
                                 title="Click to view full image in new tab"
                            >
                            <div class="mt-2">
                                <a id="modalProofLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary px-3">
                                    <i class="fas fa-external-link-alt me-1"></i> View Full Image
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label class="fw-bold small text-dark d-block mb-1">
                            <i class="fas fa-comment-dots text-primary me-1"></i> Remarks / Reason:
                        </label>
                        <div class="p-2 bg-light rounded border">
                            <div id="modalProofRemarks" style="white-space: pre-wrap; font-size: 13px;">-</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold shadow-sm" id="modalFooterPrevBtn" onclick="navigateReportProof(-1)">
                            <i class="fas fa-chevron-left me-1"></i> Prev
                        </button>
                        <span class="badge bg-white text-dark border px-2 py-1 fw-bold" id="modalProofFooterCounter" style="font-size: 12px; min-width: 65px; text-align: center;">
                            1 / 1
                        </span>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold shadow-sm" id="modalFooterNextBtn" onclick="navigateReportProof(1)">
                            Next <i class="fas fa-chevron-right ms-1"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Proofs collection populated directly from server data
        window.weeklyReportProofs = @json($proofItems ?? []);
        window.currentProofIndex = 0;

        function openReportProofModal(index) {
            var proofs = window.weeklyReportProofs || [];
            if (!proofs.length) return;

            if (index < 0) index = 0;
            if (index >= proofs.length) index = proofs.length - 1;

            window.currentProofIndex = index;
            renderProofModalItem(window.currentProofIndex);

            var modalEl = document.getElementById('reportProofModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        function navigateReportProof(step) {
            var proofs = window.weeklyReportProofs || [];
            if (proofs.length <= 1) return;

            var newIndex = window.currentProofIndex + step;
            if (newIndex >= 0 && newIndex < proofs.length) {
                window.currentProofIndex = newIndex;
                renderProofModalItem(window.currentProofIndex);
            }
        }

        function renderProofModalItem(index) {
            var proofs = window.weeklyReportProofs || [];
            var total = proofs.length;
            var item = proofs[index];
            if (!item) return;

            // Details
            document.getElementById('modalProofClient').textContent = item.client || '-';
            document.getElementById('modalProofDate').textContent = item.date || '-';

            // Image
            var imgEl = document.getElementById('modalProofImg');
            var linkEl = document.getElementById('modalProofLink');
            if (item.image) {
                imgEl.style.opacity = '0.25';
                setTimeout(function() {
                    imgEl.src = item.image;
                    imgEl.style.opacity = '1';
                }, 60);
                linkEl.href = item.image;
                linkEl.style.display = 'inline-block';
            } else {
                imgEl.src = '';
                linkEl.href = '#';
                linkEl.style.display = 'none';
            }

            // Remarks
            var remEl = document.getElementById('modalProofRemarks');
            if (item.remarks && item.remarks.trim() !== '') {
                remEl.textContent = item.remarks;
                remEl.className = 'text-dark fw-semibold';
            } else {
                remEl.textContent = 'No remarks provided';
                remEl.className = 'text-muted fst-italic';
            }

            // Counters
            var counterText = (index + 1) + ' of ' + total;
            var footerCounterText = (index + 1) + ' / ' + total;
            document.getElementById('modalProofHeaderBadge').textContent = 'Proof ' + counterText;
            document.getElementById('modalProofFooterCounter').textContent = footerCounterText;

            // Button disabled states
            var isFirst = (index <= 0);
            var isLast = (index >= total - 1);

            document.getElementById('modalProofFloatPrev').disabled = isFirst;
            document.getElementById('modalProofFloatNext').disabled = isLast;
            document.getElementById('modalFooterPrevBtn').disabled = isFirst;
            document.getElementById('modalFooterNextBtn').disabled = isLast;
            if (document.getElementById('headerProofPrevBtn')) {
                document.getElementById('headerProofPrevBtn').disabled = isFirst;
                document.getElementById('headerProofNextBtn').disabled = isLast;
            }

            // Sync dropdown if exists
            var selectEl = document.getElementById('modalProofSelect');
            if (selectEl && selectEl.value !== String(index)) {
                selectEl.value = index;
            }
        }

        // Backward compatibility
        function viewReportProof(imgSrc, clientName, dateStr, remarks) {
            var proofs = window.weeklyReportProofs || [];
            var foundIndex = proofs.findIndex(function(p) {
                return p.image === imgSrc;
            });
            if (foundIndex !== -1) {
                openReportProofModal(foundIndex);
            } else {
                window.weeklyReportProofs = [{
                    index: 0,
                    image: imgSrc,
                    client: clientName,
                    date: dateStr,
                    remarks: remarks
                }];
                openReportProofModal(0);
            }
        }

        // Setup dropdown & keyboard & touch navigation
        document.addEventListener('DOMContentLoaded', function() {
            var proofs = window.weeklyReportProofs || [];
            var selectWrapper = document.getElementById('modalProofSelectWrapper');
            var selectEl = document.getElementById('modalProofSelect');

            if (proofs.length > 1 && selectEl && selectWrapper) {
                selectWrapper.style.display = 'block';
                selectEl.innerHTML = '';
                proofs.forEach(function(p, i) {
                    var opt = document.createElement('option');
                    opt.value = i;
                    opt.textContent = (i + 1) + '. ' + p.client + ' — ' + p.date;
                    selectEl.appendChild(opt);
                });
            }

            // Keyboard navigation with Left & Right arrows
            document.addEventListener('keydown', function(e) {
                var modalEl = document.getElementById('reportProofModal');
                if (!modalEl || !modalEl.classList.contains('show')) return;

                if (e.key === 'ArrowLeft' || e.key === 'Left') {
                    e.preventDefault();
                    navigateReportProof(-1);
                } else if (e.key === 'ArrowRight' || e.key === 'Right') {
                    e.preventDefault();
                    navigateReportProof(1);
                }
            });

            // Mobile touch swipe
            var modalBody = document.querySelector('#reportProofModal .modal-body');
            if (modalBody) {
                var touchStartX = 0;
                var touchEndX = 0;
                modalBody.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                modalBody.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    var diff = touchEndX - touchStartX;
                    if (Math.abs(diff) > 40) {
                        if (diff > 0) {
                            navigateReportProof(-1);
                        } else {
                            navigateReportProof(1);
                        }
                    }
                }, { passive: true });
            }
        });
    </script>
</body>

</html>
