<header class="cst-navbar">
    <button class="cst-navbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="ti ti-menu-2" aria-hidden="true"></i>
    </button>

    <div class="cst-navbar-breadcrumb">
        @yield('breadcrumb', config('app.name', 'Construction Ready'))
    </div>

    <div class="cst-navbar-actions">
        <div class="dropdown">
            <a href="#" class="cst-navbar-avatar dropdown-toggle" data-bs-toggle="dropdown"
               id="navbarUserMenu" aria-haspopup="true" aria-expanded="false"
               style="text-decoration: none;">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserMenu">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold" style="font-size:0.875rem">{{ auth()->user()?->name }}</div>
                    <div style="font-size:0.75rem; color: var(--bs-secondary-color)">{{ auth()->user()?->email }}</div>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="ti ti-logout me-2" aria-hidden="true"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
