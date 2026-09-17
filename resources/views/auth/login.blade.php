@extends('layouts.auth')

@section('title', 'Login — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-icon">
            <i class="ti ti-building-skyscraper" aria-hidden="true"></i>
        </div>
        <span class="auth-logo-text">{{ config('app.name') }}</span>
    </div>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to manage your construction projects.</p>

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                autofocus
                autocomplete="email"
            />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                <label class="form-check-label" for="remember" style="font-size:0.85rem">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size:0.85rem; color: #f59e0b; text-decoration:none">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-auth">
            <i class="ti ti-login me-1" aria-hidden="true"></i>
            Sign In
        </button>
    </form>

    <div class="auth-footer">
        <strong>Demo accounts:</strong><br>
        admin@construction.test / owner@construction.test / contractor@construction.test<br>
        Password: <code>password</code>
    </div>
</div>
@endsection
