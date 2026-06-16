<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC - System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

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
            <div class="header">
                <h2>ULTRARITZ LENDING CORPORATION</h2>
                <h4>QUEZON CITY</h4>

                <p><strong>Loan History</strong></p>

                <p>
                    {{ $area->location_name }} [{{ $area->areas_name }}]
                </p>
            </div>


            <div class="mb-3">
                <strong>Client:</strong> {{ $client->fullname }} <br>
                <strong>Phone:</strong> {{ $client->phone }} <br>
            </div>

            <table class="table table-borderless summary-table">
                <thead>
                    <tr class="summary-header">
                        <td>PN #</td>
                        <td>Release #</td>
                        <td>From</td>
                        <td>To</td>
                        <td>Mode</td>
                        <td>Amount</td>
                        <td>Balance</td>
                        <td>Daily</td>
                        <td>Status</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td>{{ $loan->pn_number }}</td>
                            <td>{{ $loan->release_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($loan->loan_from)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($loan->loan_to)->format('M d, Y') }}</td>
                            <td>{{ ucfirst($loan->loan_status) }}</td>
                            <td>₱{{ number_format($loan->loan_amount, 2) }}</td>
                            <td>₱{{ number_format($loan->balance, 2) }}</td>
                            <td>₱{{ number_format($loan->daily, 2) }}</td>
                            <td>{{ ucfirst($loan->payment_status ?? $loan->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No loan records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>



        </section>
    </div>
    <div class="footer">
        <p>Printed on: {{ now()->format('F j, Y h:i A') }}</p>
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
