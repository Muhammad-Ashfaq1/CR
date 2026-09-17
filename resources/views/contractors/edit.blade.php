@extends('layouts.app')

@section('title', 'Edit ' . $contractor->name . ' — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-edit me-2 text-primary"></i> Edit Contractor: {{ $contractor->name }}
            </h4>
            <div class="pos-glass-intro-sub">Update contractor credentials, company name, or active standing.</div>
        </div>
        <div>
            <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Contractor Profile
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('contractors.update', $contractor) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        @if($users->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label fw-semibold">Link User Account (Optional)</label>
                                <select name="user_id" class="form-select">
                                    <option value="">No linked user / Standalone profile</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('user_id', $contractor->user_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $contractor->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Firm Name</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $contractor->company_name) }}">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $contractor->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control @error('cnic') is-invalid @enderror" value="{{ old('cnic', $contractor->cnic) }}">
                            @error('cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Office Location</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $contractor->address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Specialization / Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $contractor->notes) }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', $contractor->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActiveCheck">Contractor Active</label>
                            </div>
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
