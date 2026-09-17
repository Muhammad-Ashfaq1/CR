@php
    $current = \App\Support\AppTheme::forUser(auth()->user());
    $curVariant = $current['variant'];
    $curMode = $current['mode'];
@endphp

<li class="nav-item dropdown me-2">
    <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
       data-bs-toggle="dropdown" aria-label="Theme: {{ $curVariant }}" aria-expanded="false">
        <i class="icon-base ti tabler-palette icon-md"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="nav-theme" style="min-width: 220px;">
        <li><h6 class="dropdown-header text-uppercase fs-tiny">Theme Palette</h6></li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between @if($curVariant === 'lake') active @endif" onclick="setAppTheme('lake', '{{ $curMode }}')">
                <span><i class="icon-base ti tabler-droplet text-primary me-2"></i> Lake Blue (Default)</span>
                @if($curVariant === 'lake') <i class="icon-base ti tabler-check text-primary"></i> @endif
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between @if($curVariant === 'sky') active @endif" onclick="setAppTheme('sky', '{{ $curMode }}')">
                <span><i class="icon-base ti tabler-cloud text-info me-2"></i> Sky Teal</span>
                @if($curVariant === 'sky') <i class="icon-base ti tabler-check text-info"></i> @endif
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between @if($curVariant === 'eggplant') active @endif" onclick="setAppTheme('eggplant', '{{ $curMode }}')">
                <span><i class="icon-base ti tabler-sparkles text-dark me-2"></i> Eggplant Slate</span>
                @if($curVariant === 'eggplant') <i class="icon-base ti tabler-check"></i> @endif
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between @if($curVariant === 'dark') active @endif" onclick="setAppTheme('dark', 'dark')">
                <span><i class="icon-base ti tabler-moon text-warning me-2"></i> Dark Mode</span>
                @if($curVariant === 'dark') <i class="icon-base ti tabler-check text-warning"></i> @endif
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between @if($curVariant === 'high-contrast') active @endif" onclick="setAppTheme('high-contrast', 'dark')">
                <span><i class="icon-base ti tabler-contrast text-danger me-2"></i> High Contrast</span>
                @if($curVariant === 'high-contrast') <i class="icon-base ti tabler-check text-danger"></i> @endif
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><h6 class="dropdown-header text-uppercase fs-tiny">Appearance</h6></li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center" onclick="setAppTheme('{{ $curVariant === 'dark' || $curVariant === 'high-contrast' ? 'lake' : $curVariant }}', 'light')">
                <i class="icon-base ti tabler-sun me-2 text-warning"></i> Force Light
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center" onclick="setAppTheme('dark', 'dark')">
                <i class="icon-base ti tabler-moon-stars me-2 text-info"></i> Force Dark
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center" onclick="setAppTheme('{{ $curVariant }}', 'system')">
                <i class="icon-base ti tabler-device-laptop me-2 text-secondary"></i> System Sync
            </button>
        </li>
    </ul>
</li>

<script>
function setAppTheme(variant, mode) {
    if (window.AwtTheme) {
        window.AwtTheme.apply(variant, mode);
    }
    // Persist to server session
    fetch('{{ route("theme.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ theme_variant: variant, theme_mode: mode })
    }).then(function() {
        if (window.Notyf) {
            new Notyf().success('Theme updated: ' + variant.toUpperCase());
        }
    }).catch(function(err) {
        console.warn('Theme save failed', err);
    });
}
</script>
