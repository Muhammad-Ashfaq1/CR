@extends('layouts.app')

@section('title', 'Edit User — Admin — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Edit User: {{ $user->name }}</h1>
        <p class="cst-page-subtitle">Update account details, role permissions, and status.</p>
    </div>
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Users
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-muted small">(leave blank to keep current)</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12" id="contractorFields" style="display: {{ $user->isContractor() ? 'block' : 'none' }};">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="fw-bold mb-3 text-primary"><i class="icon-base ti tabler-briefcase me-1"></i> Contractor Profile</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company / Firm Name</label>
                                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $user->contractorProfile?->company_name) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CNIC</label>
                                        <input type="text" name="contractor_cnic" class="form-control" value="{{ old('contractor_cnic', $user->contractorProfile?->cnic) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="contractor_address" class="form-control" rows="2">{{ old('contractor_address', $user->contractorProfile?->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActiveCheck">Account Active</label>
                            </div>
                        </div>

                        <div class="col-12 text-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    const contractorFields = document.getElementById('contractorFields');

    roleSelect.addEventListener('change', function() {
        contractorFields.style.display = (this.value === 'contractor') ? 'block' : 'none';
    });
</script>
@endpush
@endsection
