@php
    $user = auth()->user();
    $awtTheme = \App\Support\AppTheme::forUser($user);
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="layout-navbar-fixed layout-menu-fixed layout-compact {{ $awtTheme['classes'] }}"
      dir="ltr"
      data-skin="default"
      data-bs-theme="{{ $awtTheme['bs_theme'] }}"
      data-awt-theme="{{ $awtTheme['variant'] }}"
      data-awt-theme-mode="{{ $awtTheme['mode'] }}"
      data-assets-path="{{ asset('assets') }}/"
      data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="awt-table-scope" content="{{ \App\Support\TableFragment::scopeToken() }}" />
    <title>@yield('title', 'Construction Ready') | {{ config('app.name', 'Construction Ready') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- PWA Head (Manifest, Icons, Meta & Service Worker) -->
    @include('layouts.partials.pwa-head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/notyf/notyf.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

    <!-- AWT & Glass UI Kit Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/awt-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/awt-glass.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tenant-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/awt-listing.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/awt-table.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/awt-menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-listing.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-navbar.css') }}" />

    @stack('styles')

    <!-- Responsive Layer & PWA Mobile Polish (Loaded Last) -->
    <link rel="stylesheet" href="{{ asset('assets/css/awt-responsive.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pwa-mobile.css') }}" />

    <!-- Theme Pre-paint Script to eliminate flicker -->
    @include('partials._theme-prepaint')
    <script src="{{ asset('assets/js/awt-theme.js') }}"></script>
    <script src="{{ asset('assets/js/awt-glass.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        #template-customizer { display: none !important; }
        .glass-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('layouts.partials.sidebar')

            <div class="layout-page">
                @include('layouts.partials.impersonation-banner')
                @include('layouts.partials.navbar')

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center shadow-sm" role="alert" style="border-left: 4px solid var(--bs-success); border-radius: 0.75rem;">
                                <i class="icon-base ti tabler-circle-check fs-4 me-2 text-success"></i>
                                <div class="flex-grow-1 fw-semibold">{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center shadow-sm" role="alert" style="border-left: 4px solid var(--bs-danger); border-radius: 0.75rem;">
                                <i class="icon-base ti tabler-alert-circle fs-4 me-2 text-danger"></i>
                                <div class="flex-grow-1 fw-semibold">{{ session('error') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-left: 4px solid var(--bs-danger); border-radius: 0.75rem;">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="icon-base ti tabler-alert-triangle fs-4 me-2 text-danger"></i>
                                    <strong class="flex-grow-1">Please correct the following errors:</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <ul class="mb-0 ps-4 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </div>

                    @include('layouts.partials.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <!-- Mobile Bottom Navigation for Handheld & Android Devices -->
    @include('layouts.partials.mobile-nav')

    <!-- Core Vendor JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- UI Kit Feedback & Confirm Dialogs -->
    <script src="{{ asset('assets/vendor/libs/notyf/notyf.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/js/awt-confirm.js') }}"></script>
    <script src="{{ asset('assets/js/notifications.js') }}"></script>
    <script src="{{ asset('assets/js/toast-helpers.js') }}"></script>
    <script src="{{ asset('assets/js/alerts.js') }}"></script>
    <script src="{{ asset('assets/js/awt-table.js') }}"></script>

    <!-- Pass Laravel Session Flash to JS & Dispatch Interactive Toasts -->
    <script>
        window.sessionMessages = window.sessionMessages || {};
        @if(session('success')) window.sessionMessages.success = @json(session('success')); @endif
        @if(session('error')) window.sessionMessages.error = @json(session('error')); @endif
        @if(session('info')) window.sessionMessages.info = @json(session('info')); @endif
        @if(session('warning')) window.sessionMessages.warning = @json(session('warning')); @endif
        @if(session('status')) window.sessionMessages.status = @json(session('status')); @endif

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.showSuccess === 'function') {
                @if(session('success')) window.showSuccess(@json(session('success'))); @endif
                @if(session('error')) window.showError(@json(session('error'))); @endif
                @if(session('info')) window.showInfo(@json(session('info'))); @endif
                @if(session('warning')) window.showWarning(@json(session('warning'))); @endif
                @if(session('status')) window.showSuccess(@json(session('status'))); @endif
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
