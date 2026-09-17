<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="cst-theme-amber">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', config('app.name', 'Construction Ready'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.0/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/cst-themes.css') }}?v={{ filemtime(public_path('assets/css/cst-themes.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/cst-glass.css') }}?v={{ filemtime(public_path('assets/css/cst-glass.css')) }}" />

    <style>
        body {
            font-family: 'Public Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f1117 0%, #1a1d2e 40%, #1f2233 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
        .auth-card {
            background: rgba(255,255,255,0.97);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        .auth-logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.6rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(245,158,11,0.4);
        }
        .auth-logo-text {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1a1d2e;
        }
        .auth-title { font-size: 1.4rem; font-weight: 700; color: #1a1d2e; margin-bottom: 0.25rem; }
        .auth-subtitle { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.75rem; }
        .form-label { font-weight: 600; font-size: 0.85rem; }
        .form-control {
            border-color: rgba(0,0,0,0.15);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            padding: 0.55rem 0.85rem;
        }
        .form-control:focus {
            border-color: rgba(245,158,11,0.5);
            box-shadow: 0 0 0 0.2rem rgba(245,158,11,0.15);
        }
        .btn-auth {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .btn-auth:hover { opacity: 0.9; color: #fff; }
        .auth-footer { margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #6b7280; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-wrapper">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
