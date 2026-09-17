@extends('layouts.app')

@section('title', 'Add User — Admin — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Add New User</h1>
        <p class="cst-page-subtitle">Create a user account with dedicated role and credentials.</p>
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
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="0300-1234567">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12" id="contractorFields" style="display: none;">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="fw-bold mb-3 text-primary"><i class="icon-base ti tabler-briefcase me-1"></i> Contractor Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company / Firm Name</label>
                                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CNIC</label>
                                        <input type="text" name="contractor_cnic" class="form-control" value="{{ old('contractor_cnic') }}" placeholder="42101-xxxxxxx-x">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="contractor_address" class="form-control" rows="2">{{ old('contractor_address') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActiveCheck">Account Active</label>
                            </div>
                        </div>

                        <div class="col-12 text-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary">Create User Account</button>
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

    function toggleContractorFields() {
        if (roleSelect.value === 'contractor') {
            contractorFields.style.display = 'block';
        } else {
            contractorFields.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', toggleContractorFields);
    toggleContractorFields();
</script>
@endpush
@endsection
