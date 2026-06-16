<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ULC System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .header h4 {
            font-size: 14px;
            margin: 0;
        }

        .header p {
            margin: 2px 0;
        }

        .summary-table td {
            padding: 4px 6px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
        }

        .table-data th {
            background: #f2f2f2;
        }


        .footer {
            margin-top: 20px;
            font-size: 11px;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>
</head>

<body>

    @php
        $totalCollectibles = $payments->sum('daily');
        $totalCollected = $payments->sum(fn($p) => is_numeric($p->collection) ? $p->collection : 0);
        $clientsPaid = $payments->filter(fn($p) => $p->collection > 0 && $p->type != 'NO PAYMENT')->count();
        $clientsNotPaid = $payments->filter(fn($p) => $p->type == 'NO PAYMENT')->count();

        $totalLapsed = 0;
        $totalNotLapsed = 0;
        foreach ($payments as $p) {
            $balance = $p->balance ?? 0;
            $hasBalance = $balance > 0;
            $today = \Carbon\Carbon::parse($p->due_date);
            $loanEnd = \Carbon\Carbon::parse($p->loan_to);
            $isLapsed = $hasBalance && $today->greaterThan($loanEnd);
            if ($isLapsed) {
                $totalLapsed++;
            } else {
                $totalNotLapsed++;
            }
        }
    @endphp

    <div class="header">
        <h2>ULTRARITZ LENDING CORPORATION</h2>
        <h4>QUEZON CITY</h4>

        <p><strong>Collection Summary</strong></p>
        <p>{{ \Carbon\Carbon::parse($payments->first()->due_date)->format('F j, Y') }}</p>

        <p>
            {{ $area->location_name }} [{{ $area->areas_name }}]
        </p>
    </div>

    <div class="mb-2">
        <strong>Reference No:</strong> {{ $referenceNumber }}
    </div>

    <table class="summary-table w-100 table table-sm table-bordered">
        <tbody>
            <tr class="table-primary">
                <td><strong>Collected By</strong></td>
                <td>{{ $payments->first()->collected_by_name ?? 'N/A' }}</td>
                <td class="text-end"><strong>Total Collectibles</strong></td>
                <td class="text-end">₱{{ number_format($totalCollectibles, 2) }}</td>
            </tr>
            <tr>
                <td><strong># Paid</strong></td>
                <td>{{ $clientsPaid }}</td>
                <td class="text-end"><strong>Total Collected</strong></td>
                <td class="text-end">₱{{ number_format($totalCollected, 2) }}</td>
            </tr>
            <tr>
                <td><strong># No Payment</strong></td>
                <td>{{ $clientsNotPaid }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong># Lapsed</strong></td>
                <td>{{ $totalLapsed }}</td>
                <td><strong># Not Lapsed</strong></td>
                <td>{{ $totalNotLapsed }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th>Client</th>
                <th>Loan</th>
                <th>Old Bal</th>
                <th>Bal</th>
                <th>Daily</th>
                <th>Collection</th>
                <th>Type</th>
                <th>Lapsed</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->fullname }}</td>
                    <td>₱{{ number_format($payment->loan_amount, 2) }}</td>
                    <td>₱{{ number_format($payment->old_balance, 2) }}</td>
                    <td>₱{{ number_format($payment->balance, 2) }}</td>
                    <td>₱{{ number_format($payment->daily, 2) }}</td>
                    <td>
                        {{ is_numeric($payment->collection) ? '₱' . number_format($payment->collection, 2) : '-' }}
                    </td>
                    <td>{{ $payment->type ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $balance = $payment->balance ?? 0;
                            $hasBalance = $balance > 0;
                            $today = \Carbon\Carbon::parse($payment->due_date);
                            $loanEnd = \Carbon\Carbon::parse($payment->loan_to);
                            $isLapsed = $hasBalance && $today->greaterThan($loanEnd);
                        @endphp
                        {{ $isLapsed ? 'YES' : 'NO' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Printed on: {{ now()->format('F j, Y h:i A') }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };

        window.onafterprint = function() {
            window.close();
        };
    </script>

</body>

</html>
