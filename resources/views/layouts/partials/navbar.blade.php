@php
    $user = auth()->user();
@endphp

<nav class="layout-navbar container-fluid navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme pos-navbar pos-tone-primary"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" aria-label="Toggle menu">
            <i class="icon-base ti tabler-menu-2 icon-md" aria-hidden="true"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <div class="pos-navbar-brand">
            <span class="pos-navbar-org-name">{{ config('app.name', 'Construction Ready') }}</span>
            <small class="pos-navbar-subtitle text-muted ms-2">Construction Project & Expense Management</small>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- PWA Install Button (shown when installable) -->
            <li class="nav-item me-2 d-none pwa-install-btn">
                <button type="button" class="btn btn-sm pwa-install-badge rounded-pill px-3 d-flex align-items-center gap-1 shadow-sm" onclick="window.triggerPwaInstall()">
                    <i class="ti ti-download fs-6"></i>
                    <span class="d-none d-sm-inline">Install App</span>
                </button>
            </li>

            @include('layouts.partials.theme-switcher')

            <li class="nav-item dropdown pos-navbar-account">
                <a class="nav-link dropdown-toggle hide-arrow p-0 pos-navbar-avatar-trigger" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Account menu" aria-expanded="false">
                    <div class="avatar avatar-online pos-navbar-avatar">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end pos-navbar-dropdown">
                    <li>
                        <div class="pos-navbar-dropdown-head">
                            <div class="avatar avatar-online pos-navbar-avatar pos-navbar-avatar--lg">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                            <div class="pos-navbar-dropdown-meta min-w-0">
                                <div class="pos-navbar-dropdown-name text-truncate">{{ $user?->name }}</div>
                                <small class="pos-navbar-dropdown-email text-truncate">{{ $user?->email }} ({{ $user?->role?->label() }})</small>
                            </div>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="icon-base ti tabler-logout me-2"></i> Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
