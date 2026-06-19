<div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form action="{{ route('admin.area.clients.update', $client->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Client Information</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                    <div class="row">
                        <!-- Client Personal Info -->
                        <div class="col-md-6 border-right">
                            <h6 class="text-primary font-weight-bold mb-3">Personal Information</h6>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" class="form-control"
                                    value="{{ $client->fullname }}" required>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $client->phone }}"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Phone 2</label>
                                <input type="text" name="phone_number_2" class="form-control"
                                    value="{{ $client->phone_number_2 }}">
                            </div>

                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" class="form-control" required>
                                    <option value="male"
                                        {{ strtolower($client->gender) == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                    <option value="female"
                                        {{ strtolower($client->gender) == 'female' ? 'selected' : '' }}>
                                        Female</option>
                                </select>
                            </div>
                        </div>

                        <!-- Latest Loan Info -->
                        <div class="col-md-6">
                            @if ($latestLoan)
                                <h6 class="text-primary font-weight-bold mb-3">Latest Loan Information</h6>
                                <input type="hidden" name="loan_id" value="{{ $latestLoan->id }}">

                                <div class="form-group">
                                    <label>PN Number *</label>
                                    <input type="text" name="pn_number" class="form-control"
                                        value="{{ $latestLoan->pn_number }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Release Number *</label>
                                    <input type="text" name="release_number" class="form-control"
                                        value="{{ $latestLoan->release_number }}" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Loan From *</label>
                                        <input type="date" name="loan_from" id="edit_info_loan_from"
                                            class="form-control" value="{{ $latestLoan->loan_from }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Loan To *</label>
                                        <input type="date" name="loan_to" id="edit_info_loan_to" class="form-control"
                                            value="{{ $latestLoan->loan_to }}" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Loan Amount *</label>
                                        <input type="number" name="loan_amount" id="edit_info_loan_amount"
                                            class="form-control" value="{{ $latestLoan->loan_amount }}" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Balance *</label>
                                        <input type="number" name="balance" class="form-control"
                                            value="{{ $latestLoan->balance }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Daily Payment *</label>
                                    <input type="number" name="daily" class="form-control"
                                        value="{{ $latestLoan->daily }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Loan Terms</label>
                                    <input type="text" name="loan_terms" class="form-control bg-gray text-white"
                                        value="100" readonly>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No loan history found for this client.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save changes
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    // Date calculation for Edit Information Modal
    document.getElementById('edit_info_loan_from').addEventListener('change', function() {
        const fromDate = this.value;
        if (fromDate) {
            const date = new Date(fromDate);
            date.setDate(date.getDate() + 100);
            const toDate = date.toISOString().split('T')[0];
            document.getElementById('edit_info_loan_to').value = toDate;
        }
    });
</script>
