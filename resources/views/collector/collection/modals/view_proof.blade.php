<!-- View Proof & Remarks Modal -->
<div class="modal fade" id="viewProofModal" tabindex="-1" role="dialog" aria-labelledby="viewProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="viewProofModalLabel">
                    <i class="fas fa-camera text-danger mr-2"></i> No Payment Proof & Remarks
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3 d-flex justify-content-between align-items-center bg-light p-2 rounded border">
                    <span class="text-muted small">Client:</span>
                    <strong class="text-dark" id="vp_client_name">-</strong>
                </div>

                <!-- Proof Image Section -->
                <div class="mb-3">
                    <label class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-image text-danger mr-1"></i> Uploaded Proof Image:
                    </label>
                    <div id="vp_image_container" class="text-center bg-light p-2 rounded border">
                        <img id="vp_image" src="" alt="Proof Image" class="img-fluid rounded shadow-sm" style="max-height: 350px; width: auto; object-fit: contain;">
                        <div class="mt-2">
                            <a id="vp_image_link" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt mr-1"></i> View Full Image
                            </a>
                        </div>
                    </div>
                    <div id="vp_no_image" class="alert alert-warning mb-0 text-center" style="display: none;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No proof image attached.
                    </div>
                </div>

                <!-- Remarks Section -->
                <div>
                    <label class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-comment-dots text-primary mr-1"></i> Remarks / Reason:
                    </label>
                    <div id="vp_remarks_container" class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap; font-size: 14px;">
                        <span id="vp_remarks_text">-</span>
                    </div>
                    <div id="vp_no_remarks" class="text-muted font-italic small" style="display: none;">
                        <i class="fas fa-info-circle mr-1"></i> No remarks provided.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
