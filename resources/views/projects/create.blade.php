@extends('layouts.app')

@section('title', 'New Project — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Create New Project</h1>
        <p class="cst-page-subtitle">Set up a construction project site, initial contractor contract, and timeline.</p>
    </div>
    <div>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Projects
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold mb-3 text-primary"><i class="ti ti-building me-2"></i> Project Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label required">Project Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Gulberg Commercial Plaza" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name') }}" placeholder="e.g. Plot 42-B, Block D">
                            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location / City</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="e.g. Main Boulevard, Lahore">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', 'active') === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(auth()->user()->isAdmin())
                            <div class="col-md-6">
                                <label class="form-label">Project Owner</label>
                                <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                    <option value="">Select Owner</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control @error('expected_completion_date') is-invalid @enderror" value="{{ old('expected_completion_date') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Scope of work, key deliverables...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-top pt-4"><i class="ti ti-user-cog me-2"></i> Initial Contractor Assignment (Optional)</h5>
                    <p class="text-muted small">You can assign a primary contractor and agree upon a fixed contract amount now or later.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Contractor</label>
                            <select name="contractor_id" class="form-select">
                                <option value="">None / Assign Later</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('contractor_id') == $contractor->id ? 'selected' : '' }}>
                                        {{ $contractor->name }} {{ $contractor->company_name ? "({$contractor->company_name})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Agreed Contract Amount (PKR)</label>
                            <input type="number" step="0.01" name="contract_amount" class="form-control" value="{{ old('contract_amount') }}" placeholder="e.g. 5000000">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Agreement Notes / Terms</label>
                            <textarea name="agreement_notes" class="form-control" rows="2" placeholder="Terms of milestone disbursements, deliverables...">{{ old('agreement_notes') }}</textarea>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-primary">Create Construction Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
