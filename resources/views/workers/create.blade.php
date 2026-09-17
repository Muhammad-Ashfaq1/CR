@extends('layouts.app')

@section('title', 'Register Worker — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-user-plus me-2 text-primary"></i> Register New Worker
            </h4>
            <div class="pos-glass-intro-sub">Add a site worker under a contractor with designated trade skill and daily wage rate.</div>
        </div>
        <div>
            <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Workers
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('workers.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor (Employer)</label>
                            <select name="contractor_id" class="form-select @error('contractor_id') is-invalid @enderror" required>
                                <option value="">Select Contractor...</option>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}" {{ old('contractor_id') == $c->id ? 'selected' : '' }}>
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
                                    <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Worker Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Muhammad Rasheed" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="03xx-xxxxxxx">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Trade / Skill Category</label>
                            <select name="worker_type" class="form-select @error('worker_type') is-invalid @enderror" required>
                                @foreach($workerTypes as $type)
                                    <option value="{{ $type }}" {{ old('worker_type', 'Laborer') === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('worker_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Initial Daily Wage (PKR)</label>
                            <input type="number" step="0.01" name="daily_wage" class="form-control fs-5 fw-bold @error('daily_wage') is-invalid @enderror" value="{{ old('daily_wage', '1500') }}" placeholder="e.g. 1500" required>
                            @error('daily_wage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', 'inactive') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Additional Info</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Experience, emergency contact, CNIC...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-check me-1"></i> Register Worker
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
