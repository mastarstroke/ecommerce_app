@extends('admin.layouts.admin')

@section('page-title', 'User Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4" id="statsCards">
    @include('admin.users.partials.stats_cards', ['stats' => $stats])
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" id="searchUser" class="form-control" placeholder="Search by name, email or phone...">
            </div>
            <div class="col-md-2">
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="verifiedFilter" class="form-select">
                    <option value="">All</option>
                    <option value="verified">Verified</option>
                    <option value="unverified">Unverified</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="loadUsers()">Filter</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Verification</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @include('admin.users.partials.table_rows', ['users' => $users])
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer" id="paginationLinks">
        {{ $users->links() }}
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_admin" class="form-check-input" value="1">
                            <label class="form-check-label">Admin User</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="verify_email" class="form-check-input" value="1">
                            <label class="form-check-label">Verify Email Immediately</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_admin" id="edit_is_admin" class="form-check-input" value="1">
                            <label class="form-check-label">Admin User</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Update User</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;

function loadUsers(page = 1) {
    currentPage = page;
    const filters = {
        search: $('#searchUser').val(),
        role: $('#roleFilter').val(),
        verified: $('#verifiedFilter').val(),
        page: page
    };
    
    $.get('{{ route("admin.users.index") }}', filters, function(response) {
        $('#usersTableBody').html($(response).find('#usersTableBody').html());
        $('#paginationLinks').html($(response).find('#paginationLinks').html());
        $('#statsCards').html($(response).find('#statsCards').html());
    });
}

function resetFilters() {
    $('#searchUser').val('');
    $('#roleFilter').val('');
    $('#verifiedFilter').val('');
    loadUsers();
}

function createUser() {
    $.ajax({
        url: '{{ route("admin.users.store") }}',
        method: 'POST',
        data: $('#createUserForm').serialize(),
        success: function(response) {
            if (response.success) {
                $('#createUserModal').modal('hide');
                $('#createUserForm')[0].reset();
                loadUsers(currentPage);
                showToast('User created successfully!', 'success');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON.errors;
            let errorMsg = '';
            $.each(errors, function(key, value) {
                errorMsg += value[0] + '\n';
            });
            showToast(errorMsg, 'danger');
        }
    });
}

function editUser(id) {
    $.get('{{ url("admin/users") }}/' + id, function(user) {
        $('#edit_user_id').val(user.id);
        $('#edit_name').val(user.name);
        $('#edit_email').val(user.email);
        $('#edit_phone').val(user.phone);
        $('#edit_address').val(user.address);
        $('#edit_is_admin').prop('checked', user.is_admin == 1);
        $('#editUserModal').modal('show');
    });
}

function updateUser() {
    const id = $('#edit_user_id').val();
    
    $.ajax({
        url: '{{ url("admin/users") }}/' + id,
        method: 'POST',
        data: $('#editUserForm').serialize(),
        success: function(response) {
            if (response.success) {
                $('#editUserModal').modal('hide');
                loadUsers(currentPage);
                showToast('User updated successfully!', 'success');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON.errors;
            let errorMsg = '';
            $.each(errors, function(key, value) {
                errorMsg += value[0] + '\n';
            });
            showToast(errorMsg, 'danger');
        }
    });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: '{{ url("admin/users") }}/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    loadUsers(currentPage);
                    showToast('User deleted successfully!', 'success');
                } else {
                    showToast(response.message, 'danger');
                }
            }
        });
    }
}

function toggleAdmin(id) {
    $.get('{{ url("admin/users/toggle-admin") }}/' + id, function(response) {
        if (response.success) {
            loadUsers(currentPage);
            showToast('Admin status updated!', 'success');
        }
    });
}

function verifyEmail(id) {
    $.get('{{ url("admin/users/verify-email") }}/' + id, function(response) {
        if (response.success) {
            loadUsers(currentPage);
            showToast('Email verified!', 'success');
        }
    });
}

$('#searchUser').keypress(function(e) {
    if (e.which == 13) loadUsers();
});
</script>
@endpush
@endsection