<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Statement of Account</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .container {
            width: 100%;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
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

        .info-box {
            width: 48%;
        }

        .line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #eaeaea;
        }

        .no-border td {
            border: none;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

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
            <div class="info-box">
                NAME: <span class="line">{{ $client->fullname }}</span>
            </div>

            <div class="info-box">
                DATE: <span class="line">{{ now()->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-box">
                PN#: <span class="line">{{ $loan->pn_number }}</span>
            </div>

            <div class="info-box">
                DURATION:
                <span class="line">
                    {{ \Carbon\Carbon::parse($loan->loan_from)->format('M d, Y') }}
                    -
                    {{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}
                </span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-box">
                FC: <span class="line">N/A</span>
            </div>
        </div>

        <!-- LOAN SUMMARY -->
        <table>
            <tr>
                <th>PN AMOUNT</th>
                <th>DUE DATE</th>
                <th>TERMS</th>
            </tr>
            <tr>
                <td>₱{{ number_format($loan->loan_amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}</td>
                <td>{{ $loan->loan_terms ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- PAYMENT TABLE -->
        @php
            $chunks = $payments->chunk(100); // 100 per page (50 left, 50 right)
        @endphp

        @foreach ($chunks as $pageIndex => $chunk)
            @php
                $left = $chunk->slice(0, 50);
                $right = $chunk->slice(50, 50);
            @endphp

            <div style="display: flex; gap: 10px; margin-top: 10px;">

                <!-- LEFT TABLE -->
                <table style="width: 50%;">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>DATE</th>
                            <th>DAILY</th>
                            <th>PAYMENT</th>
                            <th>LAPSED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($left as $index => $payment)
                            @php
                                $dueDate = \Carbon\Carbon::parse($payment->due_date);
                                $loanEnd = \Carbon\Carbon::parse($loan->loan_to);
                                $isLapsed = $dueDate->gt($loanEnd) && $loan->balance > 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $dueDate->format('M d Y') }}</td>
                                <td>{{ number_format($payment->daily ?? 0, 2) }}</td>
                                <td>
                                    {{ is_numeric($payment->collection) ? number_format($payment->collection, 2) : '-' }}
                                </td>
                                <td>{{ $isLapsed ? 'YES' : 'NO' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- RIGHT TABLE -->
                <table style="width: 50%;">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>DATE</th>
                            <th>DAILY</th>
                            <th>PAYMENT</th>
                            <th>LAPSED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($right as $index => $payment)
                            @php
                                $dueDate = \Carbon\Carbon::parse($payment->due_date);
                                $loanEnd = \Carbon\Carbon::parse($loan->loan_to);
                                $isLapsed = $dueDate->gt($loanEnd) && $loan->balance > 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 51 }}</td>
                                <td>{{ $dueDate->format('M d Y') }}</td>
                                <td>{{ number_format($payment->daily ?? 0, 2) }}</td>
                                <td>
                                    {{ is_numeric($payment->collection) ? number_format($payment->collection, 2) : '-' }}
                                </td>
                                <td>{{ $isLapsed ? 'YES' : 'NO' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <!-- PAGE BREAK -->
            <div style="page-break-after: always;"></div>
        @endforeach

    </div>
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
