@php
    $routeName = request()->route()?->getName() ?? '';
@endphp

<div class="cr-mobile-bottom-nav d-lg-none">
    <a href="{{ route('dashboard') }}" class="cr-mobile-nav-item {{ str_starts_with($routeName, 'dashboard') ? 'active' : '' }}">
        <i class="ti ti-layout-dashboard"></i>
        <span>Home</span>
    </a>

    <a href="{{ route('projects.index') }}" class="cr-mobile-nav-item {{ str_starts_with($routeName, 'projects') ? 'active' : '' }}">
        <i class="ti ti-building-community"></i>
        <span>Projects</span>
    </a>

    <a href="{{ route('attendance.index') }}" class="cr-mobile-nav-item {{ str_starts_with($routeName, 'attendance') ? 'active' : '' }}">
        <i class="ti ti-calendar-check"></i>
        <span>Attendance</span>
    </a>

    <a href="{{ route('expenses.index') }}" class="cr-mobile-nav-item {{ str_starts_with($routeName, 'expenses') ? 'active' : '' }}">
        <i class="ti ti-receipt"></i>
        <span>Expenses</span>
    </a>

    <a href="javascript:void(0);" class="cr-mobile-nav-item layout-menu-toggle">
        <i class="ti ti-menu-2"></i>
        <span>More</span>
    </a>
</div>
