@extends('layouts.app')

@section('title', 'Contractors — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-user-cog me-2 text-primary"></i> Contractors Directory
            </h4>
            <div class="pos-glass-intro-sub">Manage contractor profiles, site agreements, workforce assignments, and running ledgers.</div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createContractorModal">
                <i class="icon-base ti tabler-plus me-1"></i> Add Contractor
            </button>
        </div>
    </div>
</div>

<div class="pos-listing-panel mb-4">
    <form method="GET" action="{{ route('contractors.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label small fw-semibold">Search Contractors</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Name, firm, phone number, CNIC..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Status Filter</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Filter
            </button>
            <a href="{{ route('contractors.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="pos-listing">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Contractor & Firm</th>
                    <th>Contact</th>
                    <th>CNIC</th>
                    <th>Active Projects</th>
                    <th>Workforce</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contractors as $contractor)
                    <tr>
                        <td>
                            <a href="{{ route('contractors.show', $contractor) }}" class="fw-semibold text-primary text-decoration-none d-block">
                                {{ $contractor->name }}
                            </a>
                            <div class="small text-muted">{{ $contractor->company_name ?? 'Independent Contractor' }}</div>
                        </td>
                        <td>
                            <div><i class="icon-base ti tabler-phone me-1 text-muted"></i>{{ $contractor->phone ?? '—' }}</div>
                            @if($contractor->user?->email)
                                <div class="small text-muted">{{ $contractor->user->email }}</div>
                            @endif
                        </td>
                        <td><code>{{ $contractor->cnic ?? '—' }}</code></td>
                        <td>
                            <span class="badge bg-label-info">{{ $contractor->projects_count }} sites</span>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $contractor->workers_count }} workers</span>
                        </td>
                        <td>
                            @if($contractor->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-sm btn-primary">
                                    Ledger & Profile
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editContractorModal_{{ $contractor->id }}" title="Edit">
                                    <i class="icon-base ti tabler-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="icon-base ti tabler-user-cog fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-2">No contractors found matching criteria.</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createContractorModal">
                                Add Contractor
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($contractors->hasPages())
        <div class="p-3 border-top">
            {{ $contractors->links() }}
        </div>
    @endif
</div>

{{-- Create Contractor Modal --}}
<div class="modal fade" id="createContractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-user-plus me-2"></i> Register Contractor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractors.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        @if($users->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label fw-semibold">Link User Account (Optional)</label>
                                <select name="user_id" class="form-select">
                                    <option value="">No linked user / Standalone profile</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Tariq Mehmood" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Firm Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Tariq Builders & Co.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="0300-1234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control" placeholder="42101-xxxxxxx-x">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Office Location</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Specialization / Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Civil work, shuttering, steel binding, etc."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActCr" checked>
                                <label class="form-check-label fw-semibold" for="isActCr">Contractor Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Register Contractor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Contractor Modals --}}
@foreach($contractors as $contractor)
<div class="modal fade" id="editContractorModal_{{ $contractor->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Contractor: {{ $contractor->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractors.update', $contractor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $contractor->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Firm Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $contractor->company_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $contractor->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control" value="{{ $contractor->cnic }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Office Location</label>
                            <textarea name="address" class="form-control" rows="2">{{ $contractor->address }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Specialization / Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $contractor->notes }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActEd_{{ $contractor->id }}" {{ $contractor->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActEd_{{ $contractor->id }}">Contractor Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Are you sure you want to delete this contractor profile?')) { document.getElementById('delContForm_{{ $contractor->id }}').submit(); }">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <form id="delContForm_{{ $contractor->id }}" action="{{ route('contractors.destroy', $contractor) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
