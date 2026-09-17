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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="awt-table-scope" content="{{ \App\Support\TableFragment::scopeToken() }}" />
    <title>@yield('title', 'Construction Ready') | {{ config('app.name', 'Construction Ready') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

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
    <link rel="stylesheet" href="{{ asset('assets/css/awt-table.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/awt-menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-listing.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-navbar.css') }}" />

    @stack('styles')

    <!-- Responsive Layer (Loaded Last) -->
    <link rel="stylesheet" href="{{ asset('assets/css/awt-responsive.css') }}" />

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
                @include('layouts.partials.navbar')

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="icon-base ti tabler-check me-2 fs-5"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="icon-base ti tabler-alert-circle me-2 fs-5"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
    <script src="{{ asset('assets/js/alerts.js') }}"></script>
    <script src="{{ asset('assets/js/awt-table.js') }}"></script>

    <!-- Pass Laravel Session Flash to JS -->
    <script>
        window.sessionMessages = window.sessionMessages || {};
        @if(session('success')) window.sessionMessages.success = "{{ session('success') }}"; @endif
        @if(session('error')) window.sessionMessages.error = "{{ session('error') }}"; @endif
        @if(session('info')) window.sessionMessages.info = "{{ session('info') }}"; @endif
        @if(session('warning')) window.sessionMessages.warning = "{{ session('warning') }}"; @endif
        @if(session('status')) window.sessionMessages.status = "{{ session('status') }}"; @endif
    </script>

    @stack('scripts')
</body>
</html>
