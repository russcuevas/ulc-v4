<div class="modal fade" id="renewLoanModal" tabindex="-1" role="dialog" aria-labelledby="renewLoanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.area.clients.renew.loan.add', $client->id) }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="renewLoanModalLabel">
                        <i class="fas fa-redo"></i> Renew Loan for {{ $client->fullname }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">

                        <!-- LEFT SIDE: Client Info (read-only) -->
                        <div class="col-md-6 border-right">
                            <h6 class="text-primary font-weight-bold mb-3">Client Information</h6>

                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" class="form-control" value="{{ $client->fullname }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" value="{{ $client->phone }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Phone 2</label>
                                <input type="text" class="form-control" value="{{ $client->phone_number_2 }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Gender</label>
                                <input type="text" class="form-control" value="{{ ucfirst($client->gender) }}"
                                    readonly>
                            </div>

                            <div class="form-group">
                                <label>Date Approved</label>
                                <input type="text" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($client->created_at)->format('F d, Y') }}" readonly>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: Loan Information (inputs) -->
                        <div class="col-md-6">
                            <h6 class="text-primary font-weight-bold mb-3">Renew Loan Information</h6>

                            <div class="form-group">
                                <label>PN Number *</label>
                                <input type="text" name="pn_number" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Release Number *</label>
                                <input type="text" name="release_number" class="form-control" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Loan From *</label>
                                    <input type="date" name="loan_from" id="renew_loan_from" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Loan To *</label>
                                    <input type="date" name="loan_to" id="renew_loan_to" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Loan Amount *</label>
                                    <input type="number" name="loan_amount" id="renew_loan_amount" class="form-control" min="1"
                                        step="0.01" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Balance *</label>
                                    <input type="number" name="balance" id="renew_balance" class="form-control" min="0"
                                        step="0.01" required value="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Daily Payment *</label>
                                <input type="number" name="daily" id="renew_daily" class="form-control" min="0" step="0.01"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Loan Terms</label>
                                <input type="text" name="loan_terms" class="form-control bg-gray text-white"
                                    value="100" readonly>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Submit Renewal
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    document.getElementById('renew_loan_from').addEventListener('change', function() {
        const fromDate = this.value;
        if (fromDate) {
            const date = new Date(fromDate);
            date.setDate(date.getDate() + 100);
            const toDate = date.toISOString().split('T')[0];
            document.getElementById('renew_loan_to').value = toDate;
        }
    });

    // Auto calculate daily and balance
    const renewLoanAmountInput = document.getElementById('renew_loan_amount');
    const renewBalanceInput = document.getElementById('renew_balance');
    const renewDailyInput = document.getElementById('renew_daily');

    const renewPaymentMapping = {
        5000: 80,
        7000: 100,
        8000: 120,
        10000: 150,
        12000: 180,
        15000: 225,
        20000: 300,
        25000: 400,
        30000: 450,
        35000: 500,
        40000: 600,
        45000: 700,
        50000: 750,
        60000: 900,
        70000: 1000,
        80000: 1200,
        90000: 1400,
        100000: 1500
    };

    renewLoanAmountInput.addEventListener('input', function() {
        const amount = parseInt(this.value);

        if (renewPaymentMapping[amount]) {
            renewDailyInput.value = renewPaymentMapping[amount];
        } else if (amount > 100000) {
            // Keep it manual
        } else {
            renewDailyInput.value = '';
        }

        if (!isNaN(amount)) {
            renewBalanceInput.value = amount;
        } else {
            renewBalanceInput.value = '';
        }
    });
</script>
