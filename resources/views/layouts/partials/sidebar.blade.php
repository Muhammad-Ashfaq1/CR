@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName() ?? '';

    $isActive = fn(string $pattern): bool => str($currentRoute)->is($pattern);

    $pkrFormatter = fn(float $n): string => 'PKR ' . number_format($n, 0);
@endphp

<aside class="cst-sidebar" id="cstSidebar">
    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="cst-sidebar-brand">
        <div class="cst-sidebar-brand-icon">
            <i class="ti ti-building-skyscraper" aria-hidden="true"></i>
        </div>
        <span class="cst-sidebar-brand-text">Construction</span>
    </a>

    <nav class="cst-sidebar-nav" aria-label="Main navigation">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="cst-nav-item {{ $isActive('dashboard') ? 'active' : '' }}">
            <i class="ti ti-smart-home" aria-hidden="true"></i>
            Dashboard
        </a>

        {{-- Admin section --}}
        @if($user?->isAdmin())
            <p class="cst-nav-section-title">Administration</p>

            <a href="{{ route('admin.users.index') }}"
               class="cst-nav-item {{ $isActive('admin.users.*') ? 'active' : '' }}">
                <i class="ti ti-users" aria-hidden="true"></i>
                Users
            </a>

            <a href="{{ route('admin.expense-categories.index') }}"
               class="cst-nav-item {{ $isActive('admin.expense-categories.*') ? 'active' : '' }}">
                <i class="ti ti-category" aria-hidden="true"></i>
                Expense Categories
            </a>
        @endif

        {{-- Projects --}}
        <p class="cst-nav-section-title">Projects</p>

        <a href="{{ route('projects.index') }}"
           class="cst-nav-item {{ $isActive('projects.*') ? 'active' : '' }}">
            <i class="ti ti-building-factory-2" aria-hidden="true"></i>
            Projects
        </a>

        @if($user?->isOwner() || $user?->isAdmin())
            <a href="{{ route('contractors.index') }}"
               class="cst-nav-item {{ $isActive('contractors.*') ? 'active' : '' }}">
                <i class="ti ti-user-cog" aria-hidden="true"></i>
                Contractors
            </a>
        @endif

        {{-- Workforce --}}
        <p class="cst-nav-section-title">Workforce</p>

        <a href="{{ route('workers.index') }}"
           class="cst-nav-item {{ $isActive('workers.*') ? 'active' : '' }}">
            <i class="ti ti-hammer" aria-hidden="true"></i>
            Workers
        </a>

        <a href="{{ route('attendance.index') }}"
           class="cst-nav-item {{ $isActive('attendance.*') ? 'active' : '' }}">
            <i class="ti ti-calendar-check" aria-hidden="true"></i>
            Attendance
        </a>

        {{-- Finance --}}
        <p class="cst-nav-section-title">Finance</p>

        <a href="{{ route('expenses.index') }}"
           class="cst-nav-item {{ $isActive('expenses.*') ? 'active' : '' }}">
            <i class="ti ti-receipt" aria-hidden="true"></i>
            Expenses
        </a>

        @if($user?->isOwner() || $user?->isAdmin())
            <a href="{{ route('contractor-payments.index') }}"
               class="cst-nav-item {{ $isActive('contractor-payments.*') ? 'active' : '' }}">
                <i class="ti ti-cash" aria-hidden="true"></i>
                Contractor Payments
            </a>

            <a href="{{ route('reports.index') }}"
               class="cst-nav-item {{ $isActive('reports.*') ? 'active' : '' }}">
                <i class="ti ti-chart-bar" aria-hidden="true"></i>
                Reports
            </a>
        @endif
    </nav>

    {{-- User Footer --}}
    <div class="cst-sidebar-footer">
        <div class="cst-sidebar-user">
            <div class="cst-sidebar-avatar">
                {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-width-0">
                <div class="cst-sidebar-user-name">{{ $user?->name }}</div>
                <div class="cst-sidebar-user-role">{{ $user?->role?->label() }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ms-1">
                @csrf
                <button type="submit" class="btn btn-sm btn-icon" title="Logout"
                        style="color: rgba(160,174,192,0.6); background: none; border: none;">
                    <i class="ti ti-logout" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
