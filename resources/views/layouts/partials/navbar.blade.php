@php
    $user = auth()->user();
@endphp

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" aria-label="Open navigation menu">
            <i class="icon-base ti tabler-menu-2"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center awt-navbar-product">
            <div class="nav-item d-flex align-items-center">
                <span class="fw-semibold awt-navbar-product-name">{{ config('app.name', 'Construction Ready') }}</span>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- PWA Install Button (shown when installable) -->
            <li class="nav-item me-2 d-none pwa-install-btn">
                <button type="button" class="btn btn-sm pwa-install-badge rounded-pill px-3 d-flex align-items-center gap-1 shadow-sm" onclick="window.triggerPwaInstall()">
                    <i class="icon-base ti tabler-download fs-6"></i>
                    <span class="d-none d-sm-inline">Install App</span>
                </button>
            </li>

            @include('layouts.partials.theme-switcher')

            <li class="nav-item d-none d-md-flex align-items-center me-3 awt-navbar-org">
                <i class="icon-base ti tabler-building me-1" aria-hidden="true"></i>
                <span class="fw-semibold text-truncate awt-navbar-org-name"
                      title="{{ $user?->name ?? config('app.name') }}">{{ $user?->role?->label() ?? 'User' }}</span>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Account menu" aria-expanded="false">
                    <div class="avatar avatar-online">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <span class="fw-semibold d-block text-truncate">{{ $user?->name ?? 'Account' }}</span>
                                    <small class="text-muted text-truncate d-block">{{ $user?->email ?? '' }} ({{ $user?->role?->label() }})</small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                            <i class="icon-base ti tabler-smart-home me-2"></i>
                            <span class="align-middle">Dashboard</span>
                        </a>
                    </li>
                    @if($user?->isAdmin())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                <i class="icon-base ti tabler-users me-2"></i>
                                <span class="align-middle">Manage Users</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="icon-base ti tabler-logout me-2"></i>
                                <span class="align-middle">Sign out</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

