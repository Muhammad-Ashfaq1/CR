@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn(string $pattern): bool => str($currentRoute)->is($pattern);
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme pos-menu">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @include('layouts.partials.brand-logo', ['size' => 32])
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('app.name', 'Construction') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto" aria-label="Toggle navigation menu">
            <i class="icon-base ti tabler-chevron-left d-none d-xl-block" aria-hidden="true"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none" aria-hidden="true"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Dashboard --}}
        <li class="menu-item {{ $isActive('dashboard') || $isActive('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-smart-home" aria-hidden="true"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        {{-- Admin Section --}}
        @if($user?->isAdmin())
            <li class="menu-header small">
                <span class="menu-header-text" data-i18n="Administration">Administration</span>
            </li>

            <li class="menu-item {{ $isActive('admin.users.*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-users" aria-hidden="true"></i>
                    <div data-i18n="Users">Users</div>
                </a>
            </li>

            <li class="menu-item {{ $isActive('admin.expense-categories.*') ? 'active' : '' }}">
                <a href="{{ route('admin.expense-categories.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-category" aria-hidden="true"></i>
                    <div data-i18n="Expense Categories">Expense Categories</div>
                </a>
            </li>
        @endif

        {{-- Projects Section --}}
        <li class="menu-header small">
            <span class="menu-header-text" data-i18n="Projects">Projects</span>
        </li>

        <li class="menu-item {{ $isActive('projects.*') ? 'active' : '' }}">
            <a href="{{ route('projects.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-building-skyscraper" aria-hidden="true"></i>
                <div data-i18n="Projects">Projects</div>
            </a>
        </li>

        @if($user?->isOwner() || $user?->isAdmin())
            <li class="menu-item {{ $isActive('contractors.*') ? 'active' : '' }}">
                <a href="{{ route('contractors.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-cog" aria-hidden="true"></i>
                    <div data-i18n="Contractors">Contractors</div>
                </a>
            </li>
        @endif

        {{-- Workforce Section --}}
        <li class="menu-header small">
            <span class="menu-header-text" data-i18n="Workforce">Workforce</span>
        </li>

        <li class="menu-item {{ $isActive('workers.*') ? 'active' : '' }}">
            <a href="{{ route('workers.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-hammer" aria-hidden="true"></i>
                <div data-i18n="Workers">Workers</div>
            </a>
        </li>

        <li class="menu-item {{ $isActive('attendance.*') ? 'active' : '' }}">
            <a href="{{ route('attendance.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-calendar-check" aria-hidden="true"></i>
                <div data-i18n="Daily Attendance">Attendance</div>
            </a>
        </li>

        {{-- Finance Section --}}
        <li class="menu-header small">
            <span class="menu-header-text" data-i18n="Finance">Finance</span>
        </li>

        <li class="menu-item {{ $isActive('expenses.*') ? 'active' : '' }}">
            <a href="{{ route('expenses.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-receipt" aria-hidden="true"></i>
                <div data-i18n="Direct Expenses">Expenses</div>
            </a>
        </li>

        @if($user?->isOwner() || $user?->isAdmin())
            <li class="menu-item {{ $isActive('contractor-payments.*') ? 'active' : '' }}">
                <a href="{{ route('contractor-payments.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-cash" aria-hidden="true"></i>
                    <div data-i18n="Contractor Payments">Payments</div>
                </a>
            </li>

            <li class="menu-item {{ $isActive('reports.*') ? 'active' : '' }}">
                <a href="{{ route('reports.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-bar" aria-hidden="true"></i>
                    <div data-i18n="Reports">Reports</div>
                </a>
            </li>
        @endif

        <li class="menu-item {{ $isActive('activities.*') ? 'active' : '' }}">
            <a href="{{ route('activities.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-activity" aria-hidden="true"></i>
                <div data-i18n="Audit Trail">Audit Trail</div>
            </a>
        </li>

        <li class="menu-header small">
            <span class="menu-header-text" data-i18n="Application">App</span>
        </li>

        <li class="menu-item">
            <a href="javascript:void(0);" onclick="window.triggerPwaInstall()" class="menu-link text-warning">
                <i class="menu-icon icon-base ti tabler-download text-warning" aria-hidden="true"></i>
                <div data-i18n="Install App">Install App</div>
            </a>
        </li>
    </ul>
</aside>
