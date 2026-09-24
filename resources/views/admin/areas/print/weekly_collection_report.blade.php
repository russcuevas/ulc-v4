<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $areas_name }} - Weekly Collection Report</title>

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
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
        }

        .report-header {
            margin-bottom: 15px;
        }

        .area-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0 0 4px 0;
            color: #111;
        }

        .date-range-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }

        .weekly-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
        }

        .weekly-table th,
        .weekly-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 12px;
            line-height: 1.3;
            vertical-align: middle;
        }

        .weekly-table thead th {
            background-color: #f0f0f0 !important;
            font-weight: 700;
            text-align: center;
            border-bottom: 1.5px solid #000;
            color: #000;
        }

        .weekly-table thead th.client-col-header {
            text-align: left;
            width: 40%;
            font-size: 13px;
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
            font-size: 13px;
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

        .toolbar {
            max-width: 1000px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                padding: 3px 6px !important;
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
        }
    </style>
</head>

<body>

    <!-- Toolbar for screen view -->
    <div class="toolbar no-print">
        <a href="javascript:window.history.back()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <div class="d-flex align-items-center gap-2">
            <form action="" method="GET" class="d-flex align-items-center gap-2 m-0">
                <input type="hidden" name="from" value="{{ $fromFormatted }}">
                <input type="hidden" name="to" value="{{ $toFormatted }}">
            </form>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-print mr-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="report-wrapper">
        <div class="report-header">
            <h1 class="area-title">{{ $areas_name }}</h1>
            <div class="date-range-subtitle">
                From: {{ \Carbon\Carbon::parse($fromFormatted)->format('n/d/Y') }} To: {{ \Carbon\Carbon::parse($toFormatted)->format('n/d/Y') }}
            </div>
        </div>

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
                            No clients found for this area.
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
    </div>

</body>

</html>
