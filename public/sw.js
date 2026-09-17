const CACHE_NAME = 'cr-cache-v1';
const STATIC_ASSETS = [
  '/',
  '/dashboard',
  '/manifest.json',
  '/assets/vendor/css/core.css',
  '/assets/css/demo.css',
  '/assets/css/awt-themes.css',
  '/assets/css/awt-glass.css',
  '/assets/css/awt-table.css',
  '/assets/css/awt-menu.css',
  '/assets/css/awt-responsive.css',
  '/assets/js/awt-theme.js',
  '/assets/js/awt-glass.js',
  '/assets/js/awt-confirm.js',
  '/assets/js/notifications.js',
  '/assets/js/alerts.js',
  '/assets/img/pwa/icon-192.png',
  '/assets/img/pwa/icon-512.png',
  '/assets/img/pwa/icon-maskable-512.png',
  '/assets/img/favicon/favicon.ico'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('Some static assets could not be pre-cached during SW install:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Only handle HTTP/HTTPS and GET requests
  if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
    return;
  }

  // Network-first strategy for HTML pages / navigation
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return response;
        })
        .catch(async () => {
          const cached = await caches.match(request);
          if (cached) return cached;
          const fallback = await caches.match('/dashboard');
          return fallback || new Response(
            `<!DOCTYPE html>
            <html lang="en">
            <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Offline - Construction Ready</title>
            <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#0f172a;color:#f8fafc;text-align:center;}
            .box{padding:2rem;border-radius:1rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);max-width:400px;}</style>
            </head>
            <body><div class="box"><h2>Offline Mode</h2><p>You are currently offline. Please check your internet connection and refresh.</p><button onclick="window.location.reload()" style="background:#f59e0b;color:#000;border:none;padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;">Retry</button></div></body></html>`,
            { headers: { 'Content-Type': 'text/html' } }
          );
        })
    );
    return;
  }

  // Cache-first strategy for static assets (images, fonts, stylesheets, scripts)
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.woff2')
  ) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          // Fetch background update
          fetch(request).then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
            }
          }).catch(() => {});
          return cachedResponse;
        }
        return fetch(request).then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return response;
        });
      })
    );
    return;
  }

  // Default fetch
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
