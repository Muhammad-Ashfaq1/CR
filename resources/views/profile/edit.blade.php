@extends('layouts.app')

@section('title', 'Account Settings & Preferences')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Account & Preferences</h4>
        <div class="text-muted small">Manage your account information, security credentials, and theme settings</div>
    </div>
</div>

<div class="row g-4">
    {{-- Theme Customizer Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1"><i class="icon-base ti tabler-palette me-2 text-primary"></i> Theme & Appearance</h5>
                    <div class="text-muted small">Select your preferred color palette and contrast mode</div>
                </div>
                <span class="badge bg-label-primary">Live Preview</span>
            </div>
            <div class="card-body pt-4">
                @include('partials._theme-picker', [
                    'endpoint' => route('theme.update'),
                    'current' => \App\Support\AppTheme::forUser(auth()->user()),
                    'scope' => 'personal'
                ])
            </div>
        </div>
    </div>

    {{-- Profile Information Card --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-1"><i class="icon-base ti tabler-user me-2 text-info"></i> Profile Information</h5>
                <div class="text-muted small">Update your account's display name and email address</div>
            </div>
            <div class="card-body pt-4">
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Role</label>
                        <input type="text" class="form-control bg-light" value="{{ $user->role?->label() ?? 'Standard' }}" readonly disabled />
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Password Update Card --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-1"><i class="icon-base ti tabler-lock me-2 text-warning"></i> Update Password</h5>
                <div class="text-muted small">Ensure your account is using a long, random password to stay secure</div>
            </div>
            <div class="card-body pt-4">
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="update_password_current_password" name="current_password" autocomplete="current-password" />
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="update_password_password" name="password" autocomplete="new-password" />
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password" />
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="icon-base ti tabler-key me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
