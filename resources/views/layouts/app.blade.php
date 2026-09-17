<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="cst-theme-amber" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', config('app.name', 'Construction Ready'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.0/dist/tabler-icons.min.css" />

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" />

    <!-- App CSS Kit -->
    <link rel="stylesheet" href="{{ asset('assets/css/cst-themes.css') }}?v={{ filemtime(public_path('assets/css/cst-themes.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/cst-glass.css') }}?v={{ filemtime(public_path('assets/css/cst-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/cst-listing.css') }}?v={{ filemtime(public_path('assets/css/cst-listing.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/cst-app.css') }}?v={{ filemtime(public_path('assets/css/cst-app.css')) }}" />

    @stack('styles')
</head>
<body>

<div class="cst-layout">
    @include('layouts.partials.sidebar')

    <div class="cst-sidebar-overlay" id="sidebarOverlay"></div>

    <div class="cst-main">
        @include('layouts.partials.navbar')

        <div class="cst-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (DataTables needs it) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- Notiflix -->
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>

<script>
// Sidebar toggle
(function() {
    const sidebar = document.querySelector('.cst-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
    }

    toggleBtn?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
})();

// Notiflix config
Notiflix.Notify.init({
    position: 'right-top',
    timeout: 4000,
    borderRadius: '8px',
    fontSize: '14px',
});

// Confirm helper
window.CstConfirm = {
    open: function({ title = 'Confirm', message = 'Are you sure?', onConfirm, tone = 'danger' }) {
        Notiflix.Confirm.show(
            title,
            message,
            'Yes, Confirm',
            'Cancel',
            onConfirm,
            function() {},
            {
                titleColor: tone === 'danger' ? '#dc2626' : '#f59e0b',
                okButtonBackground: tone === 'danger' ? '#dc2626' : '#f59e0b',
                okButtonColor: '#fff',
                borderRadius: '8px',
            }
        );
    }
};

// Auto-initialize DataTables
document.querySelectorAll('table.cst-datatable').forEach(function(table) {
    $(table).DataTable({
        responsive: true,
        pageLength: 25,
        dom: 'rtip',
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            emptyTable: 'No records found',
        }
    });
});

// Wire search boxes to DataTables
document.querySelectorAll('[data-dt-search]').forEach(function(input) {
    var tableId = input.getAttribute('data-dt-search');
    var dt = $('#' + tableId).DataTable();
    input.addEventListener('input', function() {
        dt.search(this.value).draw();
    });
});
</script>

@stack('scripts')
</body>
</html>
