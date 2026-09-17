/**
 * Generic Notification System
 * Handles session flash messages and provides toast notifications
 * Uses Notyf for toast notifications
 *
 * Usage:
 * - Session flash messages: session('success'), session('error'), session('info'), session('warning')
 * - Programmatic (preferred): showSuccess('Message'), showError('Message'), showInfo(), showWarning()
 * - Or: Notification.success('Message'), Notification.error('Message') (call as Notification.success(msg) to keep "this")
 * - Delete confirmation: confirmDelete(callback, message, title) or Notification.confirmDelete(...)
 */

'use strict';

(function() {
    'use strict';

    // Initialize Notyf instance
    let notyf = null;

    /**
     * Initialize Notyf with custom configuration
     */
    function initNotyf() {
        if (typeof Notyf === 'undefined') {
            console.warn('Notyf library not found. Please include notyf.js and notyf.css');
            return null;
        }

        // Get colors from config if available, otherwise use defaults
        const colors = window.config?.colors || {
            success: '#71dd37',
            danger: '#ff3e1d',
            info: '#03c3ec',
            warning: '#ffab00'
        };

        // Custom Notyf class to allow HTML content in messages
        class CustomNotyf extends Notyf {
            _renderNotification(options) {
                const notification = super._renderNotification(options);

                // Replace textContent with innerHTML to render HTML content
                if (options.message) {
                    notification.message.innerHTML = options.message;
                }

                return notification;
            }
        }

        // Initialize CustomNotyf instance
        notyf = new CustomNotyf({
            duration: 3000,
            ripple: true,
            dismissible: true,
            position: { x: 'right', y: 'top' },
            types: [
                {
                    type: 'info',
                    background: colors.info,
                    className: 'notyf__info',
                    icon: {
                        className: 'icon-base ti tabler-info-circle-filled icon-md text-white',
                        tagName: 'i'
                    }
                },
                {
                    type: 'warning',
                    background: colors.warning,
                    className: 'notyf__warning',
                    icon: {
                        className: 'icon-base ti tabler-alert-triangle-filled icon-md text-white',
                        tagName: 'i'
                    }
                },
                {
                    type: 'success',
                    background: colors.success,
                    className: 'notyf__success',
                    icon: {
                        className: 'icon-base ti tabler-circle-check-filled icon-md text-white',
                        tagName: 'i'
                    }
                },
                {
                    type: 'error',
                    background: colors.danger,
                    className: 'notyf__error',
                    icon: {
                        className: 'icon-base ti tabler-xbox-x-filled icon-md text-white',
                        tagName: 'i'
                    }
                }
            ]
        });

        return notyf;
    }

    /**
     * Notification object with methods for displaying notifications
     */
    const Notification = {
        /**
         * Initialize the notification system
         */
        init: function() {
            if (!notyf) {
                notyf = initNotyf();
            }
            return notyf;
        },

        /**
         * Show success notification
         * @param {string} message - The message to display
         * @param {object} options - Additional options (duration, dismissible, etc.)
         */
        success: function(message, options = {}) {
            this.init();
            if (notyf) {
                notyf.open({
                    type: 'success',
                    message: message,
                    ...options
                });
            }
        },

        /**
         * Show error notification
         * @param {string} message - The message to display
         * @param {object} options - Additional options
         */
        error: function(message, options = {}) {
            this.init();
            if (notyf) {
                notyf.open({
                    type: 'error',
                    message: message,
                    ...options
                });
            }
        },

        /**
         * Show info notification
         * @param {string} message - The message to display
         * @param {object} options - Additional options
         */
        info: function(message, options = {}) {
            this.init();
            if (notyf) {
                notyf.open({
                    type: 'info',
                    message: message,
                    ...options
                });
            }
        },

        /**
         * Show warning notification
         * @param {string} message - The message to display
         * @param {object} options - Additional options
         */
        warning: function(message, options = {}) {
            this.init();
            if (notyf) {
                notyf.open({
                    type: 'warning',
                    message: message,
                    ...options
                });
            }
        },

        /**
         * Dismiss all notifications
         */
        dismissAll: function() {
            if (notyf) {
                notyf.dismissAll();
            }
        },

        /**
         * Show delete confirmation dialog using SweetAlert2
         * @param {function} callback - Callback function that receives (confirmed) boolean
         * @param {string} message - Custom message (optional)
         * @param {string} title - Custom title (optional)
         * @param {object} options - Additional SweetAlert2 options
         */
        confirmDelete: function(callback, message = null, title = null, options = {}) {
            // The application's own dialog first. It is always present (see
            // awt-confirm.js) and is the only path that is styled, focus-trapped
            // and able to show a loading state — so a native confirm() is no
            // longer an acceptable fallback for a destructive action.
            if (window.AwtConfirm) {
                window.AwtConfirm.open({
                    title: title || 'Are you sure to delete?',
                    message: message || 'You will not be able to recover this.',
                    confirmText: 'Delete',
                    tone: 'danger',
                }).then(callback);
                return;
            }

            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 library not found. Please include sweetalert2.js and sweetalert2.css');
                callback(false);
                return;
            }

            Swal.fire({
                title: title || 'Are you sure to delete?',
                text: message || 'You will not be able to recover this.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                ...options
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.dispatchEvent(new CustomEvent('sweetalert-confirm', {
                        detail: { message: 'Confirmed!' }
                    }));
                    callback(true);
                } else {
                    window.dispatchEvent(new CustomEvent('sweetalert-cancel', {
                        detail: { message: 'Cancelled!' }
                    }));
                    callback(false);
                }
            });
        },

        /**
         * Show confirmation dialog using SweetAlert2
         * @param {function} callback - Callback function that receives (confirmed) boolean
         * @param {string} message - Custom message
         * @param {string} title - Custom title
         * @param {object} options - Additional SweetAlert2 options
         */
        confirm: function(callback, message, title = 'Confirm', options = {}) {
            if (window.AwtConfirm) {
                window.AwtConfirm.open({
                    title: title,
                    message: message,
                    confirmText: 'Yes',
                    tone: 'primary',
                }).then(callback);
                return;
            }

            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 library not found. Please include sweetalert2.js and sweetalert2.css');
                callback(false);
                return;
            }

            Swal.fire({
                title: title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                ...options
            }).then(function(result) {
                if (result.isConfirmed) {
                    callback(true);
                } else {
                    callback(false);
                }
            });
        }
    };

    /**
     * Handle session flash messages
     */
    function handleSessionMessages() {
        // Check if session messages exist
        if (window.sessionMessages && typeof window.sessionMessages === 'object') {
            // Initialize Notyf if not already initialized
            Notification.init();

            // Success messages
            if (window.sessionMessages.success) {
                Notification.success(window.sessionMessages.success);
            }

            // Error messages
            if (window.sessionMessages.error) {
                Notification.error(window.sessionMessages.error);
            }

            // Info messages
            if (window.sessionMessages.info) {
                Notification.info(window.sessionMessages.info);
            }

            // Warning messages
            if (window.sessionMessages.warning) {
                Notification.warning(window.sessionMessages.warning);
            }

            // Status messages (treated as success)
            if (window.sessionMessages.status) {
                Notification.success(window.sessionMessages.status);
            }

            // Handle errors array (for validation errors)
            if (window.sessionMessages.errors && Array.isArray(window.sessionMessages.errors)) {
                window.sessionMessages.errors.forEach(function(error) {
                    Notification.error(error);
                });
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            handleSessionMessages();
        });
    } else {
        handleSessionMessages();
    }

    // Make Notification available globally
    window.Notification = Notification;

    /**
     * Application-wide toast helpers (safe to call from any script, including inline/callbacks).
     * Use these instead of Notification.success(msg) when passing as callback to avoid losing "this" context.
     *
     * Usage: showSuccess('Saved!'), showError('Failed'), showInfo('Info'), showWarning('Warning')
     */
    // The fallback is the console, never alert(). `typeof alert !== 'undefined'`
    // is true in every browser, so the old ternary meant a missing toast library
    // turned an ordinary "Saved" message into a modal dialog that blocks the tab
    // and is labelled with the hostname. A toast that fails to appear is a
    // console problem; it is not a reason to interrupt the agent.
    window.showSuccess = function(message) {
        if (Notification && typeof Notification.success === 'function') {
            Notification.success(message);
        } else {
            console.log('[awt] ' + message);
        }
    };
    window.showError = function(message) {
        if (Notification && typeof Notification.error === 'function') {
            Notification.error(message);
        } else {
            console.error('[awt] ' + message);
        }
    };
    window.showInfo = function(message) {
        if (Notification && typeof Notification.info === 'function') {
            Notification.info(message);
        } else {
            console.log('[awt] ' + message);
        }
    };
    window.showWarning = function(message) {
        if (Notification && typeof Notification.warning === 'function') {
            Notification.warning(message);
        } else {
            console.warn('[awt] ' + message);
        }
    };

    /**
     * Confirm delete dialog. Answered by the application's own dialog
     * (awt-confirm.js) — never by a native confirm().
     * Usage: confirmDelete(function(confirmed) { ... }, 'Optional message', 'Optional title');
     */
    window.confirmDelete = function(callback, message, title) {
        if (Notification && typeof Notification.confirmDelete === 'function') {
            Notification.confirmDelete(callback, message || null, title || null);
        } else if (window.AwtConfirm && typeof callback === 'function') {
            window.AwtConfirm.open({
                title: title || 'Are you sure to delete?',
                message: message || '',
                confirmText: 'Delete',
                tone: 'danger',
            }).then(callback);
        }
    };
})();
