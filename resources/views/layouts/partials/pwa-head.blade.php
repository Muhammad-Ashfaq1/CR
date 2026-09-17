{{-- PWA: web app manifest, theme color, install icons, and service worker registration. --}}
<link rel="manifest" href="{{ url('/manifest.webmanifest') }}" />
<meta name="theme-color" content="#f59e0b" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Construction Ready') }}" />
<link rel="apple-touch-icon" href="{{ asset('assets/img/pwa/apple-touch-icon.png') }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/pwa/icon-192.png') }}" />
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/img/pwa/icon-512.png') }}" />
<script>
    if ('serviceWorker' in navigator) {
        var registerSW = function() {
            navigator.serviceWorker.register('{{ asset('sw.js') }}?v={{ filemtime(public_path('sw.js')) }}', {
                scope: '/'
            }).catch(function(err) {
                console.warn('Service worker registration failed:', err);
            });
        };

        if (document.readyState === 'complete') {
            registerSW();
        } else {
            window.addEventListener('load', registerSW);
        }
    }
</script>
