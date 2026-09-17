/**
 * Progressive Web App (PWA) Registration & Install Prompt Handler
 */
(function() {
  'use strict';

  // 1. Service Worker update listener (registration handled centrally by pwa-head)
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.ready.then((reg) => {
      reg.update().catch(() => {});
    });
  }

  // 2. Install Prompt Handling
  let deferredPrompt = null;
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

  // Immediately hide install buttons if already in standalone app
  if (isStandalone) {
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.pwa-install-btn').forEach(btn => btn.classList.add('d-none'));
    });
  }

  window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent default mini-infobar on mobile Chrome
    e.preventDefault();
    deferredPrompt = e;

    // Reveal Install buttons across UI if not in standalone
    if (!isStandalone) {
      const installBtns = document.querySelectorAll('.pwa-install-btn');
      installBtns.forEach(btn => {
        btn.classList.remove('d-none');
      });
    }

    console.log('[PWA] beforeinstallprompt event captured.');
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    const installBtns = document.querySelectorAll('.pwa-install-btn');
    installBtns.forEach(btn => btn.classList.add('d-none'));

    if (window.Notyf) {
      new Notyf().success('Construction Ready installed successfully!');
    }
    console.log('[PWA] Application installed.');
  });

  // Global trigger function for button clicks
  window.triggerPwaInstall = async function() {
    if (!deferredPrompt) {
      if (isStandalone) {
        if (window.Notyf) new Notyf().open({ type: 'info', message: 'Application is already running in installed standalone mode.' });
        else alert('Application is already running in installed standalone mode.');
      } else {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
          alert('To install on iPhone/iPad: tap the Share button in Safari, then select "Add to Home Screen".');
        } else {
          alert('To install: click the Install icon (computer screen / download arrow) on the right side of the browser address bar.');
        }
      }
      return;
    }

    // Trigger Chrome native prompt
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log('[PWA] User install choice:', outcome);
    deferredPrompt = null;

    if (outcome === 'accepted') {
      const installBtns = document.querySelectorAll('.pwa-install-btn');
      installBtns.forEach(btn => btn.classList.add('d-none'));
    }
  };
})();
