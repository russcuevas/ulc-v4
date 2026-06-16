<div class="modal fade" id="editCollectorModal{{ $collector->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.collector.update', $collector->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Collector Name</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="{{ $collector->fullname }}"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $collector->email }}"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Password (Leave blank to keep current password)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
