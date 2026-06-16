<div class="modal fade" id="selectDate" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <label>Select Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>
