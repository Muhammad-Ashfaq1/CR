/**
 * Progressive Web App (PWA) Registration & Install Prompt Handler
 */
(function() {
  'use strict';

  // 1. Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then((reg) => {
          console.log('[PWA] Service Worker registered with scope:', reg.scope);
        })
        .catch((err) => {
          console.warn('[PWA] Service Worker registration failed:', err);
        });
    });
  }

  // 2. Install Prompt Handling
  let deferredPrompt = null;
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

  window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent default mini-infobar on Chrome mobile
    e.preventDefault();
    deferredPrompt = e;

    // Show Install buttons across the UI if present
    const installBtns = document.querySelectorAll('.pwa-install-btn');
    installBtns.forEach(btn => {
      btn.classList.remove('d-none');
    });

    console.log('[PWA] beforeinstallprompt captured, install prompt ready.');
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    const installBtns = document.querySelectorAll('.pwa-install-btn');
    installBtns.forEach(btn => btn.classList.add('d-none'));

    if (window.Notyf) {
      const notyf = new Notyf();
      notyf.success('Construction Ready installed successfully!');
    }
    console.log('[PWA] App successfully installed.');
  });

  // Global trigger function for button clicks
  window.triggerPwaInstall = async function() {
    if (!deferredPrompt) {
      if (isStandalone) {
        if (window.Notyf) new Notyf().open({ type: 'info', message: 'Application is already running in installed mode.' });
        else alert('Application is already running in installed mode.');
      } else {
        // Fallback instructions for manual install
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
          alert('To install on iOS: tap the Share icon at the bottom of Safari, then tap "Add to Home Screen".');
        } else {
          alert('To install: click the Install icon (computer screen / download arrow) in your browser address bar.');
        }
      }
      return;
    }

    // Show native Chrome prompt
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log('[PWA] User response to install prompt:', outcome);
    deferredPrompt = null;

    if (outcome === 'accepted') {
      const installBtns = document.querySelectorAll('.pwa-install-btn');
      installBtns.forEach(btn => btn.classList.add('d-none'));
    }
  };
})();
