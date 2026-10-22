<!-- Add Specific Client Modal -->
<div class="modal fade" id="addSpecificClientModal" tabindex="-1" role="dialog" aria-labelledby="addSpecificClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: #FF5F00;">
                <h5 class="modal-title font-weight-bold" id="addSpecificClientModalLabel">
                    <i class="fas fa-user-plus mr-2"></i> Add Specific Client from Special Area
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Info banner -->
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
                    <div>
                        <span class="text-muted small">Target Collection Sheet:</span>
                        <strong class="text-dark ml-1">{{ $area->location_name ?? '' }} - [{{ $area->areas_name ?? '' }}]</strong>
                        <span class="badge badge-light border ml-2 text-dark">{{ $refNo }}</span>
                    </div>
                    <div>
                        <span class="text-muted small">Due Date:</span>
                        <strong class="text-primary ml-1"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>
                    </div>
                </div>

                <!-- Controls & Filters -->
                <div class="row align-items-center mb-3">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1 text-muted">
                            <i class="fas fa-map-marker-alt text-danger mr-1"></i> Assigned Special Area:
                        </label>
                        <div class="p-1 px-2 border rounded bg-white d-flex align-items-center" style="height: 31px; border-color: #FF5F00 !important;">
                            <span class="badge text-white font-weight-bold mr-2" style="background-color: #FF5F00; font-size: 12px;">
                                <i class="fas fa-lock mr-1"></i> {{ $mappedSaArea->areas_name ?? $mappedSaName }}
                            </span>
                            <span class="text-dark small font-weight-bold text-truncate">
                                {{ $mappedSaArea->location_name ?? '' }}
                            </span>
                        </div>
                        <input type="hidden" id="saAreaSelect" value="{{ $mappedSaArea->id ?? '' }}">
                    </div>

                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="saClientSearch" class="small font-weight-bold mb-1 text-muted">
                            <i class="fas fa-search mr-1"></i> Search Client:
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="saClientSearch" class="form-control form-control-sm" placeholder="Type name to filter...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSaClearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-md-right">
                        <label class="small font-weight-bold mb-1 text-muted d-block">&nbsp;</label>
                        <div class="btn-group btn-group-sm" role="group" id="saFilterGroup">
                            <button type="button" class="btn btn-outline-secondary active btn-sa-filter" data-filter="all">
                                All (<span id="saCountAll">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sa-filter" data-filter="with-payment">
                                Paid (<span id="saCountWithPay">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sa-filter" data-filter="no-payment">
                                No Pay (<span id="saCountNoPay">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sa-filter" data-filter="available">
                                Available (<span id="saCountAvailable">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="saModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="color: #FF5F00 !important; width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted font-weight-500">Fetching clients from Special Area...</p>
                </div>

                <!-- Table Container -->
                <div id="saTableContainer" style="display: none; max-height: 420px; overflow-y: auto;" class="border rounded">
                    <table class="table table-sm table-hover table-striped mb-0" id="saClientsTable">
                        <thead class="thead-light sticky-top" style="z-index: 10;">
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAllSaCheckbox" title="Select all available">
                                </th>
                                <th>Client Name</th>
                                <th>Daily Collectible</th>
                                <th>Balance</th>
                                <th>Payment in Special Area</th>
                                <th style="width: 140px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="saClientsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="saEmptyState" class="text-center py-5 text-muted" style="display: none;">
                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary"></i>
                    <p class="font-weight-bold mb-1" id="saEmptyStateMsg">No clients found for this area.</p>
                    <small class="text-muted">Try choosing another area or clearing search filter.</small>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center bg-light">
                <div>
                    <span id="saSelectedCountText" class="badge badge-info p-2" style="font-size: 13px;">
                        0 client(s) selected
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm px-3 ml-2" id="btnBulkAddSaClients" style="background-color: #FF5F00; border-color: #FF5F00;" disabled>
                        <i class="fas fa-plus-circle mr-1"></i> Add Selected Clients
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
