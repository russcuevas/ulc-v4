<div class="modal fade" id="addClientModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add Client
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- FORM -->
            <form action="{{ route('secretary.area.clients.add', $id) }}" method="POST">
                @csrf

                <!-- hidden area_id input fixed to current area -->
                <input type="hidden" name="area_id" value="{{ $id }}">

                <!-- BODY -->
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-md-6 border-right">
                            <h6 class="text-primary font-weight-bold mb-3">
                                Personal Information
                            </h6>

                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="text" name="phone" class="form-control" pattern="\d{11}"
                                    maxlength="11" required>
                            </div>

                            <div class="form-group">
                                <label>Phone 2</label>
                                <input type="text" name="phone_number_2" class="form-control" pattern="\d{11}"
                                    maxlength="11">
                            </div>

                            <div class="form-group">
                                <label>Gender *</label><br>
                                <div class="form-check form-check">
                                    <input type="radio" name="gender" value="Male" checked> Male
                                </div>
                                <div class="form-check form-check">
                                    <input type="radio" name="gender" value="Female">
                                    Female
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-md-6">
                            <h6 class="text-primary font-weight-bold mb-3">
                                Loan Information
                            </h6>

                            <div class="form-group">
                                <span style="color: red;">LAST PN:
                                    [{{ \DB::table('clients_loans')->latest('id')->value('pn_number') ?? 'N/A' }}]
                                </span><br>
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
                                    <input type="date" name="loan_from" id="sec_add_loan_from" class="form-control"
                                        required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Loan To *</label>
                                    <input type="date" name="loan_to" id="sec_add_loan_to" class="form-control"
                                        required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Loan Amount *</label>
                                    <input type="number" name="loan_amount" id="sec_add_loan_amount"
                                        class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Balance *</label>
                                    <input type="number" name="balance" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Daily Payment *</label>
                                <input type="number" name="daily" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Loan Terms</label>
                                <input type="text" name="loan_terms" class="form-control bg-gray text-white"
                                    value="100" readonly>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Client
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    // Date calculation
    document.getElementById('sec_add_loan_from').addEventListener('change', function() {
        const fromDate = this.value;
        if (fromDate) {
            const date = new Date(fromDate);
            date.setDate(date.getDate() + 100);
            const toDate = date.toISOString().split('T')[0];
            document.getElementById('sec_add_loan_to').value = toDate;
        }
    });
</script>
