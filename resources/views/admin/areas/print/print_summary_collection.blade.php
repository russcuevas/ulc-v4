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

        .summary-table th,
        .summary-table td {
            white-space: nowrap;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .summary-header td {
            font-weight: 600;
            border-bottom: 2px solid #000;
        }

        .sub-row {
            font-size: 11px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <section class="invoice">

            <!-- REPORT HEADER -->
            <div class="text-center mb-4">

                <div class="fw-bold" style="font-size:16px;">
                    ULTRARITZ LENDING CORPORATION
                </div>

                <div class="fw-bold" style="font-size:14px;">
                    QUEZON CITY
                </div>

                <div class="fw-bold mt-2" style="font-size:15px;">
                    DAILY SUMMARY COLLECTION REPORT
                </div>

                <div class="mt-2" style="font-size:12px;">
                    <strong>Area:</strong> {{ $location_name }} [{{ $areas_name }}]<br>

                    DATE From: <strong>{{ \Carbon\Carbon::parse($from)->format('F d, Y') }}</strong>
                    &nbsp;&nbsp; To:
                    <strong>{{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</strong>
                </div>

            </div>


            <!-- TABLE HEADER -->
            <table class="table table-borderless summary-table">
                <tr class="summary-header text-left">
                    <td>REFERENCE #</td>
                    <td>COLLECTOR</td>
                    <td>NO OF CLIENTS</td>
                    <td>TOTAL COLLECTIBLES</td>
                    <td>TOTAL COLLECTIONS</td>
                    <td>TOTAL AMOUNT</td>
                </tr>

                @php
                    $grandActive = 0;
                    $grandSpecial = 0;
                    $grandTotal = 0;
                @endphp

                @foreach ($payments as $payment)
                    @php
                        $grandActive += $payment->active_amount;
                        $grandTotal += $payment->total_collection;
                    @endphp


                    <!-- MAIN ROW -->
                    <tr class="fw-semibold">
                        <td>{{ $payment->reference_number }}</td>
                        <td>{{ $payment->collected_by }}</td>
                        <td class="text-left">{{ $payment->total_accounts }}</td>
                        <td class="text-left">₱{{ number_format($payment->active_amount, 2) }}</td>
                        <td class="text-left">₱{{ number_format($payment->total_collection, 2) }}</td>
                        <td class="text-left">₱{{ number_format($payment->total_collection, 2) }}</td>
                    </tr>


                    <br>

                    <!-- SUB DETAILS -->
                    <tr class="sub-row">
                        <td colspan="6">
                            Cash: <strong>{{ $payment->cash_count }}</strong> |
                            Advance: <strong>{{ $payment->advance_count }}</strong> |
                            <br>
                            GCash: <strong>{{ $payment->gcash_count }}</strong> |
                            Cheque: <strong>{{ $payment->cheque_count }}</strong> |
                            No Payment: <strong>{{ $payment->no_payment_count }}</strong>
                        </td>
                    </tr>


                    <tr>
                        <td colspan="6">
                            <hr class="my-1">
                        </td>
                    </tr>
                @endforeach

                <!-- GRAND TOTAL -->
                <tr class="fw-bold border-top">
                    <td colspan="3" class="text-end">GRAND TOTAL</td>
                    <td class="text-left">₱{{ number_format($grandActive, 2) }}</td>
                    <td class="text-left"></td>
                    <td class="text-left">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>

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
