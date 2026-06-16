<div class="modal fade" id="addSecretaryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.secretary.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Secretary</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Fullname</label>
                        <input type="text" name="fullname" class="form-control" placeholder="Enter Fullname" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Enter Phone Number">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #FF5F00; border-color: #FF5F00;">Add Secretary</button>
                </div>
            </form>
        </div>
    </div>
</div>
