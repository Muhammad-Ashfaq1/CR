/**
 * AWT copy-to-clipboard — one delegated handler for the whole app.
 *
 * Any element carrying `data-awt-copy="<text>"` copies that text on click (and,
 * because it is a real <button>, on Enter/Space too — keyboard accessible for
 * free). Success and failure are announced through the shared toast API
 * (showSuccess / showError from assets/js/toast-helpers.js). Load this AFTER
 * toast-helpers.js.
 *
 *   <button type="button"
 *           data-awt-copy="https://app/meet/uuid"
 *           data-awt-copy-success="Guest meeting link copied"
 *           data-awt-copy-error="Could not copy — copy it manually">
 *
 * Nothing secret is ever placed in these attributes: the value is a public URL
 * or a public meeting code that is already shown on the page. No token, no
 * passcode, no internal id.
 */
'use strict';

(function () {
    'use strict';

    // Fallback for browsers without the async Clipboard API, and for pages served
    // over a non-secure context where navigator.clipboard is unavailable. A hidden,
    // off-screen textarea + execCommand('copy') still works there.
    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        ta.style.left = '-9999px';
        ta.style.opacity = '0';
        document.body.appendChild(ta);

        var selection = document.getSelection();
        var savedRange = selection && selection.rangeCount > 0 ? selection.getRangeAt(0) : null;

        ta.select();
        ta.setSelectionRange(0, ta.value.length);

        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }

        document.body.removeChild(ta);

        // Restore whatever the user had selected before we hijacked it.
        if (savedRange && selection) {
            selection.removeAllRanges();
            selection.addRange(savedRange);
        }

        return ok;
    }

    // Returns a Promise<boolean>. Prefers the async Clipboard API in a secure
    // context, and degrades to the textarea fallback everywhere else — including
    // when the async write is rejected (permissions, focus, etc.).
    function copyToClipboard(text) {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function' && window.isSecureContext) {
            return navigator.clipboard.writeText(text)
                .then(function () { return true; })
                .catch(function () { return fallbackCopy(text); });
        }
        return Promise.resolve(fallbackCopy(text));
    }

    // Exposed so a page with its own click wiring can reuse the same logic.
    window.awtCopyToClipboard = copyToClipboard;

    function notify(ok, successMsg, errorMsg) {
        if (ok) {
            if (typeof window.showSuccess === 'function') window.showSuccess(successMsg);
        } else if (typeof window.showError === 'function') {
            window.showError(errorMsg);
        } else if (window.AwtConfirm) {
            // Last-ditch: nothing to toast with — say so in our own dialog
            // rather than a browser one.
            window.AwtConfirm.open({
                title: 'Could not copy',
                message: errorMsg,
                confirmText: 'OK',
                cancelText: 'Close',
                tone: 'warning',
            });
        }
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('[data-awt-copy]') : null;
        if (!trigger) return;

        // A disabled control (e.g. guest access turned off) must do nothing.
        if (trigger.hasAttribute('disabled') || trigger.getAttribute('aria-disabled') === 'true') {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        var text = trigger.getAttribute('data-awt-copy') || '';
        var successMsg = trigger.getAttribute('data-awt-copy-success') || 'Link copied';
        var errorMsg = trigger.getAttribute('data-awt-copy-error') || 'Could not copy. Please copy it manually.';

        if (!text) {
            notify(false, successMsg, errorMsg);
            return;
        }

        copyToClipboard(text).then(function (ok) {
            notify(ok, successMsg, errorMsg);
        });
    });
})();
