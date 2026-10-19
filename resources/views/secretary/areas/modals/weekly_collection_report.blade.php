<!-- Weekly Collection Report Modal -->
<div class="modal fade" id="weeklyCollectionReportModal" tabindex="-1" role="dialog" aria-labelledby="weeklyCollectionReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('secretary.area.clients.weekly.collection.report', $id) }}" method="GET" target="_blank" id="secretaryWeeklyCollectionReportForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="weeklyCollectionReportModalLabel">
                        <i class="fas fa-calendar-week mr-2"></i> Weekly Collection Report
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle mr-1"></i> Area: <strong>{{ $location_name }} - [{{ $areas_name }}]</strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">From Date:</label>
                            <input type="date" name="from" class="form-control"
                                   value="{{ \Carbon\Carbon::now('Asia/Manila')->startOfWeek()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">To Date:</label>
                            <input type="date" name="to" class="form-control"
                                   value="{{ \Carbon\Carbon::now('Asia/Manila')->endOfWeek()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Report Format / Mode:</label>
                            <select name="mode" class="form-control weekly-report-mode-select">
                                <option value="detailed">Detailed (With Clients & Totals)</option>
                                <option value="summary">Summary Only (Daily Totals Only)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Client Filter / Type:</label>
                            <select name="type" class="form-control weekly-report-type-select">
                                <option value="all">All Clients</option>
                                <option value="active">Active Clients</option>
                                <option value="lapsed">Lapsed Account</option>
                            </select>
                        </div>
                    </div>

                    <!-- Client Selection -->
                    @php
                        $modalClients = isset($clients) ? collect($clients)->sortBy('fullname') : collect();
                    @endphp
                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-user text-primary mr-1"></i> Specific Client Filter:
                            </label>
                            <button type="button" class="btn btn-xs btn-outline-primary btn-toggle-multi-clients">
                                <i class="fas fa-tasks mr-1"></i> <span class="toggle-multi-text">Select Multiple Clients</span>
                            </button>
                        </div>

                        <!-- Single Client Dropdown (Default) -->
                        <div class="weekly-single-client-wrapper">
                            <select name="client_id" class="form-control weekly-client-select">
                                <option value="">-- All Clients in this Area (Default) --</option>
                                @foreach ($modalClients as $c)
                                    <option value="{{ $c->id }}">{{ $c->fullname }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-lightbulb text-warning mr-1"></i> Pumili ng specific client para sa kanya lang ang mag-generate, o iwanang "All Clients".
                            </small>
                        </div>

                        <!-- Multi Client Checklist (Hidden by default, toggled via button) -->
                        <div class="weekly-multi-client-wrapper mt-2" style="display: none;">
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="input-group input-group-sm mr-2" style="max-width: 65%;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control weekly-client-search" placeholder="Search client name...">
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-check-all-clients">All</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary btn-uncheck-all-clients">Clear</button>
                                    </div>
                                </div>

                                <div class="weekly-client-checklist-container" style="max-height: 150px; overflow-y: auto; background: #fff; padding: 6px 10px; border: 1px solid #ced4da; border-radius: 4px;">
                                    @if ($modalClients->count() > 0)
                                        @foreach ($modalClients as $c)
                                            <div class="custom-control custom-checkbox py-1 weekly-client-check-item">
                                                <input type="checkbox" name="client_ids[]" value="{{ $c->id }}" 
                                                       class="custom-control-input weekly-client-checkbox" id="sec_w_chk_{{ $c->id }}" disabled>
                                                <label class="custom-control-label font-weight-normal text-dark w-100" for="sec_w_chk_{{ $c->id }}" style="cursor: pointer;">
                                                    {{ $c->fullname }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-muted small py-2">Walang clients sa area na ito.</div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                    <span class="badge badge-primary px-2 py-1 weekly-selected-count-badge">0 clients selected</span>
                                    <button type="button" class="btn btn-xs btn-link text-primary p-0 btn-back-to-single">
                                        <i class="fas fa-arrow-left mr-1"></i> Switch back to single client
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> <strong>Detailed</strong> shows client breakdown with daily totals. <strong>Summary Only</strong> displays only total collection per day.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print mr-1"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        function initWeeklyClientFilter() {
            var $modal = $('#weeklyCollectionReportModal');
            if (!$modal.length) return;

            window.resetWeeklyReportClientFilter = function() {
                $modal.find('.weekly-multi-client-wrapper').hide();
                $modal.find('.weekly-single-client-wrapper').show();
                $modal.find('.weekly-client-select').prop('disabled', false).val('');
                $modal.find('.weekly-client-checkbox').prop('disabled', true).prop('checked', false);
                $modal.find('.weekly-selected-count-badge').text('0 clients selected');
                $modal.find('.weekly-client-search').val('');
                $modal.find('.weekly-client-check-item').show();
                $modal.find('.toggle-multi-text').text('Select Multiple Clients');
            };

            window.setWeeklyReportSingleClient = function(clientId) {
                window.resetWeeklyReportClientFilter();
                $modal.find('.weekly-client-select').val(clientId);
            };

            $modal.find('.btn-toggle-multi-clients').off('click').on('click', function(e) {
                e.preventDefault();
                var isMultiVisible = $modal.find('.weekly-multi-client-wrapper').is(':visible');
                if (isMultiVisible) {
                    window.resetWeeklyReportClientFilter();
                } else {
                    $modal.find('.weekly-single-client-wrapper').hide();
                    $modal.find('.weekly-client-select').prop('disabled', true).val('');
                    $modal.find('.weekly-multi-client-wrapper').show();
                    $modal.find('.weekly-client-checkbox').prop('disabled', false);
                    $modal.find('.toggle-multi-text').text('Single Client Select');
                }
            });

            $modal.find('.btn-back-to-single').off('click').on('click', function(e) {
                e.preventDefault();
                window.resetWeeklyReportClientFilter();
            });

            $modal.find('.weekly-client-search').off('input').on('input', function() {
                var query = $(this).val().toLowerCase().trim();
                $modal.find('.weekly-client-check-item').each(function() {
                    var name = $(this).find('label').text().toLowerCase();
                    if (name.indexOf(query) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            $modal.find('.btn-check-all-clients').off('click').on('click', function(e) {
                e.preventDefault();
                $modal.find('.weekly-client-check-item:visible .weekly-client-checkbox').prop('checked', true);
                updateCount();
            });

            $modal.find('.btn-uncheck-all-clients').off('click').on('click', function(e) {
                e.preventDefault();
                $modal.find('.weekly-client-checkbox').prop('checked', false);
                updateCount();
            });

            $modal.find('.weekly-client-checkbox').off('change').on('change', function() {
                updateCount();
            });

            function updateCount() {
                var count = $modal.find('.weekly-client-checkbox:checked').length;
                $modal.find('.weekly-selected-count-badge').text(count + (count === 1 ? ' client selected' : ' clients selected'));
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWeeklyClientFilter);
        } else {
            initWeeklyClientFilter();
        }
    })();
</script>
