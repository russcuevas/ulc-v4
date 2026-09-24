<!-- Weekly Collection Report Modal -->
<div class="modal fade" id="weeklyCollectionReportModal" tabindex="-1" role="dialog" aria-labelledby="weeklyCollectionReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('secretary.area.clients.weekly.collection.report', $id) }}" method="GET" target="_blank">
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
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Generates a weekly matrix report for all clients in this area. Unpaid days will be highlighted in red.
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
