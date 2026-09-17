@extends('layouts.app')

@section('title', 'Contractors — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Contractors Directory</h1>
        <p class="cst-page-subtitle">Manage contractor profiles, agreements, active workforce, and running ledgers.</p>
    </div>
    <div>
        <a href="{{ route('contractors.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Contractor
        </a>
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('contractors.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Contractor name, company, phone, CNIC..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('contractors.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="cst-card">
    <div class="cst-card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Contractor & Firm</th>
                        <th>Contact</th>
                        <th>CNIC</th>
                        <th>Active Projects</th>
                        <th>Workers</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractors as $contractor)
                        <tr>
                            <td>
                                <a href="{{ route('contractors.show', $contractor) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                    {{ $contractor->name }}
                                </a>
                                <div class="small text-muted">{{ $contractor->company_name ?? 'Individual Contractor' }}</div>
                            </td>
                            <td>
                                <div><i class="ti ti-phone me-1 text-muted"></i>{{ $contractor->phone ?? '—' }}</div>
                            </td>
                            <td><code>{{ $contractor->cnic ?? '—' }}</code></td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $contractor->projects_count }} sites</span>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $contractor->workers_count }} workers</span>
                            </td>
                            <td>
                                @if($contractor->is_active)
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-sm btn-primary">
                                    Ledger & Profile
                                </a>
                                <a href="{{ route('contractors.edit', $contractor) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-user-cog fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No contractors found.</p>
                                <a href="{{ route('contractors.create') }}" class="btn btn-sm btn-primary">Add Contractor</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($contractors->hasPages())
        <div class="cst-card-footer p-3">
            {{ $contractors->links() }}
        </div>
    @endif
</div>
@endsection
