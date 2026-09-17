@extends('layouts.app')

@section('title', 'Add Contractor — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-user-plus me-2 text-primary"></i> Register Contractor
            </h4>
            <div class="pos-glass-intro-sub">Register a contractor, link user credentials, and capture contact info.</div>
        </div>
        <div>
            <a href="{{ route('contractors.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Contractors
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('contractors.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        @if($users->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label fw-semibold">Link User Account (Optional)</label>
                                <select name="user_id" class="form-select">
                                    <option value="">No linked user / Standalone profile</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Tariq Mehmood" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Firm Name</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="e.g. Tariq Builders & Co.">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="0300-1234567">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control @error('cnic') is-invalid @enderror" value="{{ old('cnic') }}" placeholder="42101-xxxxxxx-x">
                            @error('cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Office Location</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Specialization / Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Civil work, shuttering, steel binding, etc.">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActiveCheck">Contractor Active</label>
                            </div>
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-check me-1"></i> Save Contractor Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
