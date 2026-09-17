/**
 * Shared toast / notification helpers – one API for the whole application.
 * Prefers Notiflix.Notify when available, then Notification (Notyf), then the
 * console. Deliberately NOT alert(): a toast that degrades into a modal browser
 * dialog blocks the tab and reads as a browser warning rather than as this app.
 *
 * Usage (use these everywhere instead of notyf.success, notyf.failure, etc.):
 *
 *   showSuccess('Saved successfully');
 *   showError('Something went wrong');
 *   showWarning('Please check the form');
 *   showInfo('Tip: You can export data here');
 *   confirmDelete(function(confirmed) { if (confirmed) deleteItem(); }, 'You will not recover this.', 'Delete?');
 *
 * Replacements for existing code:
 *   notyf.success(msg)  → showSuccess(msg)
 *   notyf.failure(msg)  → showError(msg)
 *   notyf.warning(msg)  → showWarning(msg)
 *   notyf.info(msg)     → showInfo(msg)
 *
 * No need to define a local notyf or check window.Notiflix – just call showSuccess/showError etc.
 *
 * Load after: Notiflix Notify (if used), and/or notifications.js (Notyf).
 * For Notiflix toasts: include Notiflix Notify script (e.g. notiflix-notify-aio.js or CDN) before this file.
 */

'use strict';

(function() {
    'use strict';

    function useNotiflix() {
        return typeof window.Notiflix === 'object' && window.Notiflix.Notify &&
            typeof window.Notiflix.Notify.success === 'function';
    }

    function useNotification() {
        return typeof window.Notification === 'object' &&
            typeof window.Notification.success === 'function';
    }

    // Last resort only. Every layout loads a toast library, so reaching this
    // means something failed to load — which is a console problem, not a reason
    // to interrupt the agent with a native dialog.
    function fallbackAlert(msg) {
        console.warn('[awt] ' + msg);
    }

    // Single API: prefer Notiflix.Notify, then Notification (Notyf), then the
    // console. Load this after Notiflix/notifications.js.
    window.showSuccess = function(message) {
        if (useNotiflix()) {
            window.Notiflix.Notify.success(message);
        } else if (useNotification()) {
            window.Notification.success(message);
        } else {
            fallbackAlert(message);
        }
    };

    window.showError = function(message) {
        if (useNotiflix()) {
            window.Notiflix.Notify.failure(message);
        } else if (useNotification()) {
            window.Notification.error(message);
        } else {
            fallbackAlert(message);
        }
    };

    window.showWarning = function(message) {
        if (useNotiflix()) {
            window.Notiflix.Notify.warning(message);
        } else if (useNotification()) {
            window.Notification.warning(message);
        } else {
            fallbackAlert(message);
        }
    };

    window.showInfo = function(message) {
        if (useNotiflix()) {
            window.Notiflix.Notify.info(message);
        } else if (useNotification()) {
            window.Notification.info(message);
        } else {
            fallbackAlert(message);
        }
    };

    // This file loads AFTER notifications.js, so this definition is the one the
    // application ends up calling. It therefore has to make the same choice that
    // notifications.js does: the application's own dialog first.
    //
    // The SweetAlert2 branch below is kept only for a page that somehow has
    // neither — it is a second confirmation dialog with its own look, and every
    // layout loads awt-confirm.js before this file, so in practice it is never
    // the one that answers.
    window.confirmDelete = function(callback, message, title) {
        if (typeof callback !== 'function') return;
        if (window.AwtConfirm) {
            window.AwtConfirm.open({
                title: title || 'Are you sure to delete?',
                message: message || 'You will not be able to recover this.',
                confirmText: 'Delete',
                tone: 'danger',
            }).then(callback);
        } else if (window.Notification && typeof window.Notification.confirmDelete === 'function') {
            window.Notification.confirmDelete(callback, message || null, title || null);
        } else if (typeof window.Swal !== 'undefined') {
            window.Swal.fire({
                title: title || 'Are you sure to delete?',
                text: message || 'You will not be able to recover this.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                callback(result.isConfirmed === true);
            });
        }
    };

    window.confirmImpersonate = function(name, role, onConfirm) {
        if (typeof onConfirm !== 'function') return;
        var title = 'Impersonate ' + (role ? role : 'User') + '?';
        var message = 'You will experience the application as ' + name + ' (' + (role || 'User') + '). You can exit back to your administrator account at any time.';

        if (typeof window.Swal !== 'undefined') {
            window.Swal.fire({
                title: title,
                html: 'You will experience the application as <strong>' + name + '</strong> (' + (role || 'User') + ').<br><br><small class="text-muted">You can exit back to your administrator account at any time.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="icon-base ti tabler-user-check me-1"></i> Yes, Impersonate',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed === true) {
                    onConfirm();
                }
            });
        } else if (window.AwtConfirm) {
            window.AwtConfirm.open({
                title: title,
                message: message,
                confirmText: 'Impersonate',
                tone: 'primary',
            }).then(function(confirmed) {
                if (confirmed) onConfirm();
            });
        } else {
            if (confirm(message)) {
                onConfirm();
            }
        }
    };
})();
