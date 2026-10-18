<!-- No Payment Modal -->
<div class="modal fade" id="noPaymentModal" tabindex="-1" role="dialog" aria-labelledby="noPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="noPaymentModalLabel">
                    <i class="fas fa-times-circle me-2"></i> Tag as NO PAYMENT
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="noPaymentForm" action="{{ route('collector.collections.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Client Details Card -->
                    <div class="card bg-light border-0 mb-3 shadow-none">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Client Name:</span>
                                <strong class="text-dark" id="np_modal_client_name">-</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Daily Collectible:</span>
                                <strong class="text-danger" id="np_modal_daily">₱0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Balance:</span>
                                <strong class="text-dark" id="np_modal_balance">₱0.00</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="client_id" id="np_client_id" value="">
                    <input type="hidden" name="loan_id" id="np_loan_id" value="">
                    <input type="hidden" name="area_id" id="np_area_id" value="">
                    <input type="hidden" name="reference_no" id="np_reference_no" value="{{ $refNo }}">
                    <input type="hidden" name="due_date" id="np_due_date" value="{{ $selectedDate }}">
                    <input type="hidden" name="old_balance" id="np_old_balance" value="0">
                    <input type="hidden" name="daily" id="np_daily" value="0">
                    <input type="hidden" name="type" value="NO PAYMENT">
                    <input type="hidden" name="collection" value="">
                    <input type="hidden" name="savings" value="0">

                    <!-- Image Upload (Required) -->
                    <div class="form-group mb-3">
                        <label for="np_image" class="font-weight-bold text-dark d-flex justify-content-between">
                            <span><i class="fas fa-camera text-danger mr-1"></i> Proof Image <span class="text-danger">*</span></span>
                            <small class="text-danger font-weight-normal">Required</small>
                        </label>
                        <div class="custom-file">
                            <input type="file" name="image" class="custom-file-input" id="np_image" accept="image/*" capture="environment" required>
                            <label class="custom-file-label" for="np_image" id="np_image_label">Take photo or choose image...</label>
                        </div>
                        <small class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle"></i> Upload proof of no payment (e.g., closed store, client absent, house photo).
                        </small>

                        <!-- Image Preview Box -->
                        <div id="np_preview_container" class="mt-2 text-center position-relative" style="display: none; background: #f8f9fa; border: 1px dashed #ced4da; border-radius: 6px; padding: 8px;">
                            <img id="np_preview_img" src="" alt="Proof Preview" class="img-fluid rounded shadow-sm" style="max-height: 200px; width: auto; object-fit: contain;">
                            <button type="button" id="np_remove_img_btn" class="btn btn-sm btn-danger position-absolute" style="top: 8px; right: 10px; border-radius: 50%; width: 28px; height: 28px; padding: 0;" title="Remove image">
                                <i class="fas fa-times"></i>
                            </button>
                            <div id="np_compression_info" class="mt-2 text-center" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Remarks (Optional / Nullable) -->
                    <div class="form-group mb-2">
                        <label for="np_remarks" class="font-weight-bold text-dark d-flex justify-content-between">
                            <span><i class="fas fa-comment-dots text-primary mr-1"></i> Remarks</span>
                            <small class="text-muted font-weight-normal">Optional</small>
                        </label>
                        <textarea name="remarks" id="np_remarks" class="form-control" rows="3" placeholder="Enter remarks or explanation (optional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4" id="npSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save Collection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
