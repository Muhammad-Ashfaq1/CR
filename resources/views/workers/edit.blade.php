@extends('layouts.app')

@section('title', 'Edit ' . $worker->name . ' — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-edit me-2 text-primary"></i> Edit Worker: {{ $worker->name }}
            </h4>
            <div class="pos-glass-intro-sub">Update trade skill, contact info, or revise daily wage (wage revisions are logged automatically).</div>
        </div>
        <div>
            <a href="{{ route('workers.show', $worker) }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Worker Profile
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('workers.update', $worker) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor (Employer)</label>
                            <select name="contractor_id" class="form-select @error('contractor_id') is-invalid @enderror" required>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}" {{ old('contractor_id', $worker->contractor_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->company_name ?? 'Individual' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('contractor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default Assigned Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Any Project / Site Pool</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $worker->project_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Worker Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $worker->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $worker->phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Trade / Skill Category</label>
                            <select name="worker_type" class="form-select @error('worker_type') is-invalid @enderror" required>
                                @foreach($workerTypes as $type)
                                    <option value="{{ $type }}" {{ old('worker_type', $worker->worker_type) === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('worker_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Daily Wage Rate (PKR)</label>
                            <input type="number" step="0.01" name="daily_wage" class="form-control fs-5 fw-bold @error('daily_wage') is-invalid @enderror" value="{{ old('daily_wage', $worker->daily_wage) }}" required>
                            @error('daily_wage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-2 text-primary"><i class="icon-base ti tabler-history me-1"></i> Wage Revision Details (If changing rate)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Effective From Date</label>
                                    <input type="date" name="wage_effective_date" class="form-control" value="{{ old('wage_effective_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Reason for Change</label>
                                    <input type="text" name="wage_change_reason" class="form-control" placeholder="e.g. Rate revision, seniority promotion...">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $worker->joining_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $worker->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $worker->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Additional Info</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $worker->notes) }}</textarea>
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
