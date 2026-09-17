@extends('layouts.app')

@section('title', 'Users — Admin — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-users fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">User Management</h4>
                <p class="awt-dash-subtitle mb-0">Manage platform accounts, security credentials, role assignments, and active standing.</p>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="icon-base ti tabler-user-plus me-1"></i> Add User
            </button>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted text-uppercase">Search User</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted text-uppercase">Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted text-uppercase">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="border-bottom">
                <tr>
                    <th class="text-uppercase small fw-semibold text-muted ps-3">User Profile</th>
                    <th class="text-uppercase small fw-semibold text-muted">Role</th>
                    <th class="text-uppercase small fw-semibold text-muted">Phone</th>
                    <th class="text-uppercase small fw-semibold text-muted">Status</th>
                    <th class="text-uppercase small fw-semibold text-muted">Joined</th>
                    <th class="text-uppercase small fw-semibold text-muted text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary fw-semibold">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold text-body">{{ $u->name }}</div>
                                    <div class="small text-muted">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $u->role->value === 'admin' ? 'bg-label-danger' : ($u->role->value === 'owner' ? 'bg-label-primary' : 'bg-label-warning') }}">
                                {{ $u->role->label() }}
                            </span>
                        </td>
                        <td>{{ $u->phone ?? '—' }}</td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $u->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-3">
                            @if($u->id !== auth()->id() && !$u->isAdmin())
                                <form id="impUsrForm_{{ $u->id }}" action="{{ route('impersonate.user', $u) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-icon btn-text-warning rounded-pill" title="Impersonate {{ $u->name }}" onclick="confirmImpersonate('{{ addslashes($u->name) }}', '{{ $u->role->label() }}', () => document.getElementById('impUsrForm_{{ $u->id }}').submit())">
                                        <i class="icon-base ti tabler-user-check"></i>
                                    </button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editUserModal_{{ $u->id }}" title="Edit User">
                                <i class="icon-base ti tabler-edit"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="awt-empty-state">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-user-off fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No users found</h6>
                                <p class="text-muted small mb-0">Try changing your search keywords or role filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-3 border-top">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- Create User Modal --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-user-plus me-2"></i> Add Platform User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Asif Khan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="user@domain.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Role</label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="03xx-xxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="usrActCr" checked>
                                <label class="form-check-label fw-semibold" for="usrActCr">Account Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modals --}}
@foreach($users as $u)
<div class="modal fade" id="editUserModal_{{ $u->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit User: {{ $u->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.update', $u) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Role</label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" {{ $u->role->value === $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $u->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reset Password (Leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Reset Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="usrActEd_{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="usrActEd_{{ $u->id }}">Account Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    @if($u->id !== auth()->id())
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(() => document.getElementById('delUsrForm_{{ $u->id }}').submit(), 'Delete user {{ $u->name }}? This will revoke system access.')">
                            <i class="icon-base ti tabler-trash me-1"></i> Delete
                        </button>
                    @else
                        <div></div>
                    @endif
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            @if($u->id !== auth()->id())
                <form id="delUsrForm_{{ $u->id }}" action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach

@push('scripts')
@if(request('action') === 'create')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('createUserModal');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@elseif(request('edit'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('editUserModal_{{ request('edit') }}');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@endif
@endpush
@endsection
