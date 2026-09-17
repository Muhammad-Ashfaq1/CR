@extends('layouts.auth')

@section('title', 'Sign in — ' . config('app.name', 'Construction Ready'))

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
        <div class="card">
            <div class="card-body">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-6">
                    <a href="{{ url('/') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            @include('layouts.partials.brand-logo', ['size' => 36])
                        </span>
                        <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('app.name', 'Construction Ready') }}</span>
                    </a>
                </div>
                <!-- /Logo -->

                <h4 class="mb-1">Welcome back!</h4>
                <p class="mb-4 text-muted small">Sign in to manage projects, workforce attendance, and expense ledgers.</p>

                @if ($errors->any())
                    <div class="alert alert-danger p-2 mb-4 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', 'owner@construction.test') }}" placeholder="Enter your email" required autofocus />
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                        </div>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" class="form-control" name="password" placeholder="••••••••" value="password" required />
                        </div>
                    </div>

                    <div class="my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" checked />
                            <label class="form-check-label" for="remember-me">Remember Me</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100" type="submit">Sign In</button>
                    </div>
                </form>

                {{-- Demo Logins Quick Switcher --}}
                <div class="mt-4 p-3 bg-light rounded border">
                    <div class="small fw-bold text-dark mb-2">Demo Credentials (Password: <code>password</code>)</div>
                    <div class="d-flex flex-column gap-1 small text-muted">
                        <div>&bull; Owner: <code>owner@construction.test</code></div>
                        <div>&bull; Contractor: <code>contractor@construction.test</code></div>
                        <div>&bull; Admin: <code>admin@construction.test</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
