<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC - System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/print.css') }}">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 12px;
        }

        .summary-table td {
            padding: 6px 8px;
            vertical-align: middle;
        }

        .summary-header td {
            font-weight: 600;
            border-bottom: 2px solid #000;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <section class="invoice">
            <div class="text-center mb-4">

                <div class="fw-bold" style="font-size:16px;">
                    ULTRARITZ LENDING CORPORATION
                </div>

                <div class="fw-bold" style="font-size:14px;">
                    QUEZON CITY
                </div>

                <div class="fw-bold mt-2" style="font-size:15px;">
                    SALES REPORT
                </div>

                <div class="mt-2" style="font-size:12px;">
                    <strong>AREA:</strong> {{ $allAreas ? 'ALL AREAS' : $loans->first()->areas_name ?? '' }} <br>

                    DATE From: <strong>{{ \Carbon\Carbon::parse($from)->format('F d, Y') }}</strong>
                    To: <strong>{{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</strong>
                </div>
            </div>

            <!-- TABLE HEADER -->
            <table class="table table-borderless summary-table">
                <thead>
                    <tr class="summary-header">
                        <td>PN No</td>
                        <td>Mode</td>
                        <td>Released Date</td>
                        <td>Client Name</td>
                        <td>Area</td>
                        <td>From</td>
                        <td>To</td>
                        <td>Daily</td>
                        <td>PN Amount</td>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $totalDaily = 0;
                        $totalAmount = 0;
                        $newCount = 0;
                        $renewalCount = 0;
                        $clientIds = [];
                    @endphp

                    @foreach ($loans as $loan)
                        <tr>
                            <td>{{ $loan->pn_number }}</td>
                            <td class="text-uppercase">{{ $loan->loan_status }}</td>
                            <td>{{ \Carbon\Carbon::parse($loan->created_at)->format('m/d/Y') }}</td>
                            <td>{{ $loan->fullname }}</td>
                            <td>{{ $loan->areas_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($loan->loan_from)->format('m/d/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($loan->loan_to)->format('m/d/Y') }}</td>
                            <td>₱{{ number_format($loan->daily, 2) }}</td>
                            <td>₱{{ number_format($loan->loan_amount, 2) }}</td>
                        </tr>

                        @php
                            $totalDaily += $loan->daily;
                            $totalAmount += $loan->loan_amount;

                            if ($loan->loan_status === 'new') {
                                $newCount++;
                            }
                            if ($loan->loan_status === 'renewal') {
                                $renewalCount++;
                            }

                            $clientIds[] = $loan->fullname;
                        @endphp
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="summary-header">
                        <td colspan="9"></td>
                    </tr>

                    <tr>
                        <td colspan="3"><strong>NO. OF CLIENTS</strong></td>
                        <td colspan="6">{{ count(array_unique($clientIds)) }}</td>
                    </tr>

                    <tr>
                        <td><strong>NEW</strong></td>
                        <td colspan="8">[{{ $newCount }}]</td>
                    </tr>

                    <tr>
                        <td><strong>RENEWAL</strong></td>
                        <td colspan="8">[{{ $renewalCount }}]</td>
                    </tr>

                    <tr>
                        <td colspan="7" class="text-end"><strong>TOTAL DAILY</strong></td>
                        <td colspan="2" class="text-end">₱{{ number_format($totalDaily, 2) }}</td>
                    </tr>

                    <tr>
                        <td colspan="7" class="text-end"><strong>TOTAL PN AMOUNT</strong></td>
                        <td colspan="2" class="text-end">₱{{ number_format($totalAmount, 2) }}</td>
                    </tr>

                    <tr>
                        <td colspan="7" class="text-end"><strong>DEBIT/CREDIT</strong></td>
                        <td colspan="2" class="text-end">₱{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

        </section>
    </div>

    <script>
        window.addEventListener("load", () => {
            window.print();
        });

        window.onafterprint = function() {
            window.close();
        };
    </script>

</body>

</html>
