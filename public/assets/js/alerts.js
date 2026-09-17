/**
 * Generic Alert Helper Functions
 * Provides common alert and confirmation dialogs
 * Extends the Notification system with additional helper methods
 *
 * Usage:
 * - Alerts.showDeleteConfirmation(callback, message, title)
 * - Alerts.showConfirmation(callback, message, title)
 * - Alerts.showSuccess(message, title)
 * - Alerts.showError(message, title)
 * - Alerts.showInfo(message, title)
 * - Alerts.showWarning(message, title)
 */

'use strict';

(function() {
    'use strict';

    const Alerts = {
        /**
         * Show delete confirmation dialog
         * @param {function} callback - Callback function that receives (confirmed) boolean
         * @param {string} message - Custom message (optional)
         * @param {string} title - Custom title (optional)
         */
        showDeleteConfirmation: function(callback, message = null, title = null) {
            if (window.Notification) {
                window.Notification.confirmDelete(callback, message, title);
            } else {
                console.error('Notification system not initialized');
            }
        },

        /**
         * Show general confirmation dialog
         * @param {function} callback - Callback function that receives (confirmed) boolean
         * @param {string} message - Message to display
         * @param {string} title - Title (optional, defaults to 'Confirm')
         */
        showConfirmation: function(callback, message, title = 'Confirm') {
            if (window.Notification) {
                window.Notification.confirm(callback, message, title);
            } else {
                console.error('Notification system not initialized');
            }
        },

        /**
         * Show success alert dialog (SweetAlert2)
         * @param {string} message - Message to display
         * @param {string} title - Title (optional)
         */
        showSuccess: function(message, title = 'Success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: title,
                    text: message
                });
            } else if (window.Notification) {
                window.Notification.success(message);
            }
        },

        /**
         * Show error alert dialog (SweetAlert2)
         * @param {string} message - Message to display
         * @param {string} title - Title (optional)
         */
        showError: function(message, title = 'Error') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message
                });
            } else if (window.Notification) {
                window.Notification.error(message);
            }
        },

        /**
         * Show info alert dialog (SweetAlert2)
         * @param {string} message - Message to display
         * @param {string} title - Title (optional)
         */
        showInfo: function(message, title = 'Information') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: title,
                    text: message
                });
            } else if (window.Notification) {
                window.Notification.info(message);
            }
        },

        /**
         * Show warning alert dialog (SweetAlert2)
         * @param {string} message - Message to display
         * @param {string} title - Title (optional)
         */
        showWarning: function(message, title = 'Warning') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: message
                });
            } else if (window.Notification) {
                window.Notification.warning(message);
            }
        }
    };

    // Make Alerts available globally
    window.Alerts = Alerts;

    // Also add to Helpers if it exists (for backward compatibility)
    if (window.Helpers && typeof window.Helpers === 'object') {
        window.Helpers.showDeleteConfirmationAlert = function(callback, message, title) {
            Alerts.showDeleteConfirmation(callback, message, title);
        };
    }
})();

/* ---------------------------------------------------------------------------
 * Auto-dismiss success alerts (application-wide)
 *
 * Any Bootstrap `.alert-success` — whether rendered on page load or injected
 * later by AJAX handlers — automatically fades out and is removed after 3s.
 * Works without touching individual call sites. Opt a specific alert out with
 * the `data-no-auto-dismiss` attribute.
 * ------------------------------------------------------------------------- */
(function () {
    'use strict';

    var DISMISS_AFTER_MS = 3000;
    var SCHEDULED_FLAG = 'autoDismissScheduled'; // → data-auto-dismiss-scheduled

    function scheduleDismiss(el) {
        if (!el || el.nodeType !== 1 || !el.classList) return;
        if (!el.classList.contains('alert-success')) return;
        if (el.hasAttribute('data-no-auto-dismiss')) return;
        if (el.dataset[SCHEDULED_FLAG]) return; // already scheduled
        el.dataset[SCHEDULED_FLAG] = '1';

        setTimeout(function () {
            if (!el.isConnected) return; // user already closed / navigated
            try {
                if (window.bootstrap && bootstrap.Alert) {
                    // Triggers the standard fade-out + DOM removal + close events.
                    bootstrap.Alert.getOrCreateInstance(el).close();
                    return;
                }
            } catch (e) { /* fall through to manual removal */ }
            // Fallback when Bootstrap's Alert API is unavailable.
            el.classList.remove('show');
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 200);
        }, DISMISS_AFTER_MS);
    }

    function scan(root) {
        if (!root || root.nodeType !== 1) return;
        if (root.classList && root.classList.contains('alert-success')) {
            scheduleDismiss(root);
        }
        if (root.querySelectorAll) {
            root.querySelectorAll('.alert-success').forEach(scheduleDismiss);
        }
    }

    function init() {
        scan(document.body);
        // Catch alerts added after load (AJAX success handlers, modals, etc.).
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var added = mutations[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    scan(added[j]);
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
