/**
 * AwtPhone — shared phone input (intl-tel-input v29 + E.164 hidden field).
 *
 * Markup contract (from <x-awt-phone-input>):
 *   [data-awt-phone]            visible tel input
 *   [data-awt-phone-e164]       hidden input that is actually submitted
 *   [data-awt-phone-error]      inline validation message
 *   data-awt-phone-region       ISO default country (lowercase preferred)
 *   data-awt-phone-initial      optional E.164 to seed
 *
 * Load intlTelInputWithUtils.min.js before this file (utils bundled).
 * Non-phone identifiers (SIP, short codes, emergency) stay on dedicated paths.
 */
(function () {
    'use strict';

    if (window.AwtPhone && window.AwtPhone.__bound) {
        return;
    }

    function detectDefaultCountry(fallback) {
        fallback = (fallback || 'us').toLowerCase();
        try {
            var lang = (navigator.language || navigator.userLanguage || '').toLowerCase();
            var match = lang.match(/-([a-z]{2})$/i);
            if (match) {
                return match[1].toLowerCase();
            }
            if (lang.length === 2) {
                return lang;
            }
        } catch (e) { /* ignore */ }
        return fallback;
    }

    function fieldRoot(input) {
        return input.closest('[data-awt-phone-field]') || input.parentElement;
    }

    function errorBox(input) {
        var field = fieldRoot(input);
        return field ? field.querySelector('[data-awt-phone-error]') : null;
    }

    function hiddenE164(input) {
        var field = fieldRoot(input);
        return field ? field.querySelector('[data-awt-phone-e164]') : null;
    }

    function setError(input, message) {
        var box = errorBox(input);
        input.classList.toggle('is-invalid', !!message);
        if (!box) return;
        if (message) {
            box.hidden = false;
            box.textContent = message;
        } else {
            box.hidden = true;
            box.textContent = '';
        }
    }

    function sync(input, iti, options) {
        options = options || {};
        var hidden = hiddenE164(input);
        if (!hidden) return { ok: true, e164: '' };

        var raw = String(input.value || '').trim();
        if (raw === '') {
            hidden.value = '';
            if (input.getAttribute('data-awt-phone-required') === '1') {
                if (!options.soft) {
                    setError(input, 'Enter a phone number.');
                }
                return { ok: false, e164: '' };
            }
            setError(input, '');
            return { ok: true, e164: '' };
        }

        if (!iti) {
            if (!options.soft) {
                setError(input, 'Phone input is not ready.');
            }
            return { ok: false, e164: '' };
        }

        if (typeof iti.isValidNumber === 'function' && iti.isValidNumber()) {
            var e164 = iti.getNumber();
            hidden.value = e164 || '';
            setError(input, '');
            return { ok: true, e164: hidden.value };
        }

        var err = 'Enter a valid phone number for the selected country.';
        try {
            if (typeof iti.getValidationError === 'function') {
                var code = iti.getValidationError();
                if (code === 'TOO_SHORT') err = 'This number is too short.';
                else if (code === 'TOO_LONG') err = 'This number is too long.';
                else if (code === 'INVALID_COUNTRY_CODE') err = 'Select a valid country code.';
            }
        } catch (e) { /* keep default */ }

        if (options.soft) {
            // While typing: do not wipe a previously valid E.164 on every
            // keystroke — autosave FormData collectors would persist blanks.
            return { ok: false, e164: String(hidden.value || '') };
        }

        hidden.value = '';
        setError(input, err);
        return { ok: false, e164: '' };
    }

    function initOne(input) {
        if (!input) return null;
        if (input.__awtPhone) return input.__awtPhone;
        if (typeof window.intlTelInput !== 'function') {
            console.warn('AwtPhone: intlTelInput is not loaded');
            return null;
        }

        var region = detectDefaultCountry((input.getAttribute('data-awt-phone-region') || 'us').toLowerCase());
        var initial = input.getAttribute('data-awt-phone-initial') || '';

        var iti = window.intlTelInput(input, {
            initialCountry: region,
            countryOrder: ['us', 'ca', 'gb', 'au'],
            countrySearch: true,
            formatAsYouType: true,
            numberDisplayFormat: 'NATIONAL',
            placeholderNumberPolicy: 'POLITE',
            strictMode: false,
            separateDialCode: false,
            // Deliberately NOT overriding where the country list is rendered.
            // At small widths this build switches the selector to its
            // full-screen mode and appends it to <body>, so it already escapes
            // the scrolling modal body it would otherwise be clipped by; at
            // larger widths the modal does not scroll, so there is nothing to
            // escape. (The option is `dropdownParent` in this version, not the
            // `dropdownContainer` of older releases — worth knowing before
            // reaching for it.)
            // Utils ship in intlTelInputWithUtils.min.js — no loadUtils needed.
        });

        if (initial) {
            try { iti.setNumber(initial); } catch (e) { /* ignore */ }
        }

        var onChange = function () { sync(input, iti); };
        input.addEventListener('blur', onChange);
        input.addEventListener('change', onChange);
        input.addEventListener('countrychange', onChange);
        input.addEventListener('input', function () {
            if (input.classList.contains('is-invalid')) {
                setError(input, '');
            }
            // Soft-sync so hidden E.164 is ready for autosave FormData collectors.
            sync(input, iti, { soft: true });
        });

        var form = input.closest('form');
        if (form && !form.__awtPhoneSubmitBound) {
            form.__awtPhoneSubmitBound = true;
            form.addEventListener('submit', function (event) {
                var phones = form.querySelectorAll('[data-awt-phone]');
                var ok = true;
                phones.forEach(function (el) {
                    var inst = el.__awtPhone;
                    var result = sync(el, inst && inst.iti);
                    if (!result.ok) ok = false;
                });
                if (!ok) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
        }

        Promise.resolve(iti.promise || true).then(function () {
            sync(input, iti, { soft: true });
        }).catch(function () {
            sync(input, iti, { soft: true });
        });

        input.__awtPhone = {
            iti: iti,
            sync: function (opts) { return sync(input, iti, opts); },
            e164: function () {
                var h = hiddenE164(input);
                return h ? String(h.value || '') : '';
            }
        };
        return input.__awtPhone;
    }

    function init(root) {
        root = root || document;
        var nodes = root.querySelectorAll ? root.querySelectorAll('[data-awt-phone]') : [];
        Array.prototype.forEach.call(nodes, initOne);
    }

    function syncAll(root) {
        root = root || document;
        var nodes = root.querySelectorAll ? root.querySelectorAll('[data-awt-phone]') : [];
        var ok = true;
        Array.prototype.forEach.call(nodes, function (el) {
            var result = window.AwtPhone.sync(el);
            if (result && !result.ok) ok = false;
        });
        return ok;
    }

    function boot() {
        init(document);
    }

    document.addEventListener('DOMContentLoaded', boot);
    document.addEventListener('turbo:load', boot);
    document.addEventListener('turbo:frame-load', function (e) {
        init(e.target || document);
    });

    window.AwtPhone = {
        __bound: true,
        init: init,
        initOne: initOne,
        syncAll: syncAll,
        sync: function (input, opts) {
            return sync(input, input && input.__awtPhone && input.__awtPhone.iti, opts);
        },
        /** Prefer the hidden E.164; fall back to formatting the visible value. */
        value: function (input) {
            if (!input) return '';
            var hidden = hiddenE164(input);
            if (hidden && hidden.value) return String(hidden.value);
            if (input.__awtPhone) {
                var r = input.__awtPhone.sync({ soft: true });
                if (r && r.e164) return r.e164;
            }
            return window.AwtPhone.toE164(input.value || '');
        },
        /**
         * Normalize a raw string to E.164 when utils are available; otherwise a
         * best-effort US/NANP fallback.
         */
        toE164: function (value, defaultCountry) {
            var raw = String(value == null ? '' : value).trim();
            if (raw === '') return '';

            var utils = window.intlTelInput && window.intlTelInput.utils;
            if (utils && typeof utils.formatNumber === 'function') {
                try {
                    var country = (defaultCountry || 'US').toUpperCase();
                    var iso2 = country.length === 2 ? country.toLowerCase() : 'us';
                    var number = utils.formatNumber(raw, iso2, 'E164');
                    if (number && utils.isValidNumber(number, iso2)) {
                        return number;
                    }
                } catch (e) { /* fall through */ }
            }

            var hasPlus = raw.charAt(0) === '+';
            var digits = raw.replace(/\D/g, '');
            if (digits === '') return '';
            if (hasPlus) return '+' + digits;
            if (digits.length === 10) return '+1' + digits;
            if (digits.length === 11 && digits.charAt(0) === '1') return '+' + digits;
            return '+' + digits;
        },
        sameNumber: function (a, b, defaultCountry) {
            var left = window.AwtPhone.toE164(a, defaultCountry);
            return left !== '' && left === window.AwtPhone.toE164(b, defaultCountry);
        },
        toDisplay: function (value, defaultCountry) {
            var e164 = window.AwtPhone.toE164(value, defaultCountry);
            if (!e164) return String(value == null ? '' : value).trim();
            var utils = window.intlTelInput && window.intlTelInput.utils;
            if (utils && typeof utils.formatNumber === 'function') {
                try {
                    var region = (defaultCountry || 'US').toLowerCase();
                    return utils.formatNumber(e164, region, 'NATIONAL') || e164;
                } catch (e) { /* fall through */ }
            }
            return e164;
        }
    };
})();
