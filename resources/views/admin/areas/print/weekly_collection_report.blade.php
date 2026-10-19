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
                                    @endphp
                                    <td class="no-payment-cell">
                                        <div class="no-payment-content">
                                            @if ($hasImage)
                                                <div class="no-payment-img-wrap mb-1">
                                                    <img src="{{ $imageSrc }}" 
                                                         alt="Proof" 
                                                         class="no-payment-thumb"
                                                         title="Click to view full image"
                                                         onclick="viewReportProof('{{ $imageSrc }}', '{{ addslashes($row->fullname) }}', '{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}', '{{ addslashes($remarksText) }}')"
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

    <!-- View Proof Modal -->
    <div class="modal fade no-print" id="reportProofModal" tabindex="-1" aria-labelledby="reportProofModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title fw-bold" id="reportProofModalLabel">
                        <i class="fas fa-camera text-danger me-2"></i> No Payment Proof & Remarks
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
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
                        <label class="fw-bold small text-dark d-block text-start mb-1">
                            <i class="fas fa-image text-danger me-1"></i> Uploaded Proof Image:
                        </label>
                        <div class="bg-light p-2 rounded border">
                            <img id="modalProofImg" src="" alt="Proof Image" class="img-fluid rounded" style="max-height: 380px; width: auto; object-fit: contain;">
                            <div class="mt-2">
                                <a id="modalProofLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
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
                <div class="modal-footer py-1 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewReportProof(imgSrc, clientName, dateStr, remarks) {
            document.getElementById('modalProofClient').textContent = clientName || '-';
            document.getElementById('modalProofDate').textContent = dateStr || '-';
            document.getElementById('modalProofImg').src = imgSrc;
            document.getElementById('modalProofLink').href = imgSrc;
            
            var remEl = document.getElementById('modalProofRemarks');
            if (remarks && remarks.trim() !== '') {
                remEl.textContent = remarks;
                remEl.className = 'text-dark';
            } else {
                remEl.textContent = 'No remarks provided';
                remEl.className = 'text-muted fst-italic';
            }

            var modalEl = document.getElementById('reportProofModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    </script>
</body>

</html>
