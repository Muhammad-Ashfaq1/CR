/* ============================================================================
 * awt-mobile-view-gestures.js — native-feeling row gestures on touch phones
 * ----------------------------------------------------------------------------
 * The rc-* list screens (SMS conversations, Voicemail, Recent calls) already
 * collapse to a single pane below md and open a bottom sheet on a row tap — see
 * awt-master-detail.js. What they still lacked is what a phone user reaches for
 * without thinking: swipe a row sideways to act on it, press and hold it for a
 * menu.
 *
 * This adds exactly that, and nothing else:
 *
 *   • swipe LEFT  past 80px → the row's destructive action (Delete / Clear)
 *   • swipe RIGHT past 80px → the row's status action  (Mark read / Flag / Dial)
 *   • press and hold 500ms  → the floating action sheet (#mobileActionSheet)
 *
 * ── Why it cannot touch anything else ───────────────────────────────────────
 *
 * ISOLATION. Every listener returns immediately unless BOTH
 * matchMedia('(max-width: 768px)') matches AND the device reports touch. On a
 * desktop browser nothing below the guard ever runs: no handler is bound to a
 * row, no element is inserted, no class is added. The stylesheet is the same
 * story — every rule in awt-mobile-view-gestures.css lives inside the same
 * media query. Mouse clicks, hover states and desktop table layouts are
 * therefore untouched by construction, not by care.
 *
 * SCROLLING WINS TIES. The first ~10px of a drag decide its axis. If the
 * vertical component is greater or equal, the gesture is abandoned for the rest
 * of that touch and preventDefault() is never called — the list scrolls exactly
 * as it does today. Only once the drag is decisively horizontal do we take it,
 * and the rows carry `touch-action: pan-y` so the browser itself keeps vertical
 * panning native. Multi-touch (pinch-zoom) aborts on sight.
 *
 * THE EXISTING CONTROLS STILL OWN THEIR TAPS. A touch that starts on a button,
 * link, input, checkbox or the row's audio player is not a gesture — those
 * behave precisely as before. And because a committed swipe or a long-press is
 * followed by a synthetic click from the browser, we swallow that one click in
 * the capture phase, which is what stops awt-master-detail.js from also opening
 * the detail sheet behind our action.
 *
 * NO DOM RESTRUCTURING. Rows are not wrapped — several modules select rows with
 * `$list.children(...)`, and a wrapper would break them. The two coloured
 * action zones are appended INSIDE the row, parked just outside its left and
 * right edges (`right: 100%` / `left: 100%`). Because they are children they
 * travel with the row's transform, so sliding the row is all it takes to reveal
 * them. They are removed again when the row settles.
 *
 * NO NEW ENDPOINTS. Every action either replays a click on the control the row
 * already renders (so the module's own handler, confirm dialog and toast run
 * unchanged), or calls the same REST endpoint that control would have called,
 * with the same method and the same payload. URLs are read from the container
 * rather than hardcoded.
 *
 * Out of scope on purpose: the global WebRTC dialer, Reverb channels, the bell
 * counters, and every <audio> element. This file dials only by clicking the
 * row's existing Call button, and never touches a media element.
 *
 * Registration — a list opts in with two markers and no per-row work beyond a
 * flag:
 *
 *   <div id="rcVoicemailList" data-awt-gestures="voicemail"
 *        data-awt-gestures-delete-url="…" data-awt-gestures-flag-url="…">
 *     <div class="rc-call-item" data-mobile-gesture="true" data-vm-id="7">…</div>
 *   </div>
 * ==========================================================================*/
(function () {
    'use strict';

    if (window.AwtMobileGestures) return;      // already loaded

    /** The one breakpoint this file exists below. Mirrored in the stylesheet. */
    var MEDIA_QUERY = '(max-width: 768px)';

    /** Hold this long, without wandering, to summon the action sheet. */
    var LONG_PRESS_MS = 500;

    /** Slop before a drag has an axis — and before a long press is off. */
    var AXIS_SLOP_PX = 10;

    /** How far a row must travel for its action to fire on release. */
    var COMMIT_PX = 80;

    /** Past this the row stops following the finger, so it can't be flung off. */
    var MAX_PULL_PX = 120;

    /** Long enough to swallow the synthetic click a touch sequence emits. */
    var GHOST_CLICK_MS = 350;

    /**
     * Controls that mean something on their own.
     *
     * A touch that begins on the Play button, the select checkbox, the Call
     * icon or a dropdown toggle must do that and only that — dragging the row
     * out from under a finger that was aiming at a button is how a gesture
     * layer earns its reputation. The row itself may be a <button> in some
     * lists, so only a control NESTED inside the row disqualifies the touch.
     */
    var CONTROL_SELECTOR = 'button, a[href], input, select, textarea, label, audio, video, ' +
        '[role="button"], [contenteditable], [data-awt-md-ignore]';

    // ── Environment gates ───────────────────────────────────────────────────

    function isMobileViewport() {
        return !!(window.matchMedia && window.matchMedia(MEDIA_QUERY).matches);
    }

    function isTouchDevice() {
        return ('ontouchstart' in window) || (navigator.maxTouchPoints || 0) > 0;
    }

    /**
     * Asked afresh at the start of every touch rather than cached at load, so
     * a rotation, a window resize or a desktop browser's device-emulation
     * toggle lands on the right answer without a reload.
     */
    function gesturesApply() {
        return isMobileViewport() && isTouchDevice();
    }

    // ── Small shared helpers ────────────────────────────────────────────────

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Replay a click on a control the row already renders.
     *
     * Flagged so our own ghost-click suppressor lets it through — the
     * suppressor is a capture listener on document and would otherwise eat the
     * very click we just dispatched.
     */
    function synthClick(el) {
        if (!el) return false;

        var ev = new MouseEvent('click', { bubbles: true, cancelable: true });
        ev.awtGestureSynthetic = true;
        el.dispatchEvent(ev);

        return true;
    }

    /**
     * The same JSON call the list's own bulk control makes: same URL, same
     * method, same body shape. Nothing here invents an endpoint.
     */
    function sendJson(url, method, payload) {
        if (!url) return Promise.reject(new Error('missing url'));

        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);

            return res;
        });
    }

    function toastError(message) {
        if (typeof window.showError === 'function') window.showError(message);
    }

    function toastSuccess(message) {
        if (typeof window.showSuccess === 'function') window.showSuccess(message);
    }

    /**
     * Tell the shell its counters moved.
     *
     * The badge code owns its own refresh; we only raise the same signals the
     * existing handlers raise, and never write a badge number ourselves.
     */
    function refreshCounters() {
        document.dispatchEvent(new CustomEvent('awt:unread-counts-updated'));
        if (window.AwtSidebarBadges && typeof window.AwtSidebarBadges.refresh === 'function') {
            window.AwtSidebarBadges.refresh();
        }
    }

    function confirmThen(options) {
        if (window.AwtConfirm && typeof window.AwtConfirm.open === 'function') {
            return window.AwtConfirm.open(options);
        }

        return Promise.resolve(window.confirm(options.message || 'Are you sure?'));
    }

    function copyToClipboard(text) {
        if (!text) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                toastSuccess('Number copied');
            }).catch(function () {
                toastError('Copy failed');
            });

            return;
        }

        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); toastSuccess('Number copied'); } catch (e) { /* nothing to do */ }
        document.body.removeChild(ta);
    }

    function removeRow(row, list) {
        row.remove();
        // Let the module's own empty-state logic notice; a poll or the next
        // render replaces the list wholesale anyway.
        if (list && !list.querySelector('[data-mobile-gesture="true"]')) {
            document.dispatchEvent(new CustomEvent('awt:gestures:list-emptied', { detail: { list: list } }));
        }
    }

    // ── Module registry ─────────────────────────────────────────────────────
    //
    // One descriptor per list that opted in. `left` is the destructive edge,
    // `right` the status edge; `sheet` builds the long-press menu. Every action
    // receives the row and its container, and returns nothing — each is
    // responsible for its own feedback.

    /** Conversations use the config object the messaging page already sets. */
    function textBaseUrl() {
        var cfg = window.TenantMessagingConfig || {};

        return cfg.baseUrl || '/text';
    }

    function convId(row) {
        return row.getAttribute('data-id');
    }

    function convPhone(row) {
        return row.getAttribute('data-phone') || '';
    }

    function convIsUnread(row) {
        return row.classList.contains('rc-conv-unread');
    }

    /**
     * Mark a conversation read.
     *
     * The row renders a "Mark as unread" menu item but no "mark read" — reading
     * normally happens by opening the thread. So this posts to the very
     * endpoint opening the thread posts to, and then strips the unread styling
     * locally so the list looks right before the next poll confirms it.
     */
    function convMarkRead(row) {
        var id = convId(row);
        if (!id) return;

        sendJson(textBaseUrl() + '/conversations/' + id + '/read', 'POST', {}).then(function () {
            row.classList.remove('rc-conv-unread');
            var dot = row.querySelector('.rc-unread-dot');
            if (dot) dot.remove();
            toastSuccess('Marked as read');
            refreshCounters();
        }).catch(function () {
            toastError('Failed to mark as read');
        });
    }

    function convToggleRead(row) {
        if (convIsUnread(row)) {
            convMarkRead(row);

            return;
        }

        // Its own handler owns the request, the toast and the list reload.
        if (!synthClick(row.querySelector('.rc-conv-mark-unread'))) {
            toastError('Mark as unread is unavailable for this conversation');
        }
    }

    function vmId(row) {
        return row.getAttribute('data-vm-id');
    }

    function historyId(row) {
        return row.getAttribute('data-rc-id');
    }

    function rowNumber(row) {
        return row.getAttribute('data-phone')
            || row.getAttribute('data-vm-number')
            || row.getAttribute('data-rc-number')
            || '';
    }

    var MODULES = {
        conversations: {
            label: 'conversation',
            left: {
                label: 'Delete',
                icon: 'tabler-trash',
                // Its own handler already confirms, deletes and reloads.
                run: function (row) {
                    if (!synthClick(row.querySelector('.rc-conv-delete'))) {
                        toastError('Delete is unavailable for this conversation');
                    }
                },
            },
            right: {
                label: function (row) { return convIsUnread(row) ? 'Mark read' : 'Mark unread'; },
                icon: function (row) { return convIsUnread(row) ? 'tabler-mail-opened' : 'tabler-mail'; },
                run: convToggleRead,
            },
            sheet: function (row) {
                return [
                    {
                        label: 'Quick Reply', icon: 'tabler-message-reply', run: function () {
                            synthClick(row);
                            // The thread pane renders after the click; give it a
                            // frame before reaching for the composer.
                            window.setTimeout(function () {
                                var box = document.getElementById('threadReplyBody');
                                if (box) box.focus();
                            }, 250);
                        },
                    },
                    {
                        label: 'Edit Contact', icon: 'tabler-user-edit',
                        // Present only while the number has no contact yet —
                        // exactly when the row renders the Save-contact menu.
                        enabled: !!row.querySelector('.awt-act-create-contact'),
                        run: function () { synthClick(row.querySelector('.awt-act-create-contact')); },
                    },
                    {
                        label: function () { return convIsUnread(row) ? 'Mark Read' : 'Mark Unread'; },
                        icon: 'tabler-flag',
                        run: function () { convToggleRead(row); },
                    },
                    {
                        label: 'Mute', icon: 'tabler-bell-off',
                        enabled: !!row.querySelector('.rc-conv-mute'),
                        run: function () { synthClick(row.querySelector('.rc-conv-mute')); },
                    },
                    {
                        label: 'Copy Number', icon: 'tabler-copy',
                        run: function () { copyToClipboard(convPhone(row)); },
                    },
                    {
                        label: 'Delete', icon: 'tabler-trash', tone: 'danger',
                        run: function () { synthClick(row.querySelector('.rc-conv-delete')); },
                    },
                ];
            },
        },

        voicemail: {
            label: 'voicemail',
            left: {
                label: 'Delete',
                icon: 'tabler-trash',
                run: function (row, list) {
                    var id = vmId(row);
                    if (!id) return;

                    confirmThen({
                        title: 'Delete voicemail',
                        message: 'This removes the message from your mailbox.',
                        confirmText: 'Delete',
                        tone: 'danger',
                    }).then(function (ok) {
                        if (!ok) return;

                        sendJson(list.getAttribute('data-awt-gestures-delete-url'), 'DELETE', { ids: [Number(id)] })
                            .then(function () {
                                removeRow(row, list);
                                toastSuccess('Voicemail deleted');
                                refreshCounters();
                            })
                            .catch(function () { toastError('Could not delete the voicemail.'); });
                    });
                },
            },
            right: {
                // The mailbox has no read/unread write of its own — playing a
                // message is what marks it heard, server-side. Flag is the one
                // per-row status this list can actually set, so that is what
                // this edge does, and it says so.
                label: function (row) { return row.getAttribute('data-vm-flagged') === '1' ? 'Unflag' : 'Flag'; },
                icon: 'tabler-flag',
                run: function (row, list) {
                    var id = vmId(row);
                    if (!id) return;

                    var flagged = row.getAttribute('data-vm-flagged') !== '1';
                    sendJson(list.getAttribute('data-awt-gestures-flag-url'), 'PATCH', { ids: [Number(id)], flagged: flagged })
                        .then(function () {
                            row.setAttribute('data-vm-flagged', flagged ? '1' : '0');
                            var mark = row.querySelector('.rc-vm-flag-mark');
                            if (mark) mark.classList.toggle('d-none', !flagged);
                            toastSuccess(flagged ? 'Voicemail flagged' : 'Flag removed');
                        })
                        .catch(function () { toastError('Could not update the voicemail.'); });
                },
            },
            sheet: function (row, list) {
                var audio = row.getAttribute('data-vm-audio') || '';

                return [
                    {
                        label: 'Call Back', icon: 'tabler-phone',
                        enabled: !!row.querySelector('.rc-call-quick-dial'),
                        run: function () { synthClick(row.querySelector('.rc-call-quick-dial')); },
                    },
                    {
                        label: 'Send Message', icon: 'tabler-message',
                        enabled: !!row.querySelector('.rc-call-quick-text'),
                        run: function () { synthClick(row.querySelector('.rc-call-quick-text')); },
                    },
                    {
                        label: 'Download Recording', icon: 'tabler-download',
                        enabled: !!audio,
                        // A plain download link. The row's <audio> element and
                        // the detail player are not touched.
                        run: function () {
                            var a = document.createElement('a');
                            a.href = audio;
                            a.download = '';
                            a.rel = 'noopener';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        },
                    },
                    {
                        label: 'Copy Number', icon: 'tabler-copy',
                        run: function () { copyToClipboard(row.getAttribute('data-vm-number') || ''); },
                    },
                    {
                        label: 'Delete', icon: 'tabler-trash', tone: 'danger',
                        run: function () { MODULES.voicemail.left.run(row, list); },
                    },
                ];
            },
        },

        history: {
            label: 'call',
            left: {
                label: 'Clear',
                icon: 'tabler-trash',
                run: function (row, list) {
                    var id = historyId(row);
                    if (!id) return;

                    confirmThen({
                        title: 'Clear call',
                        message: 'This removes the entry from your call history.',
                        confirmText: 'Clear',
                        tone: 'danger',
                    }).then(function (ok) {
                        if (!ok) return;

                        sendJson(list.getAttribute('data-awt-gestures-delete-url'), 'DELETE', { ids: [Number(id)] })
                            .then(function () {
                                removeRow(row, list);
                                toastSuccess('Call cleared');
                                refreshCounters();
                            })
                            .catch(function () { toastError('Could not clear the call.'); });
                    });
                },
            },
            right: {
                label: 'Call',
                icon: 'tabler-phone',
                // Dialling is the global dialer's job, reached the only way this
                // file ever reaches it: by clicking the button the row renders.
                run: function (row) {
                    if (!synthClick(row.querySelector('.rc-call-quick-dial'))) {
                        toastError('No number to dial for this call');
                    }
                },
            },
            sheet: function (row, list) {
                return [
                    {
                        label: 'Call Back', icon: 'tabler-phone',
                        enabled: !!row.querySelector('.rc-call-quick-dial'),
                        run: function () { synthClick(row.querySelector('.rc-call-quick-dial')); },
                    },
                    {
                        label: 'Send Message', icon: 'tabler-message',
                        enabled: !!row.querySelector('.rc-call-quick-text'),
                        run: function () { synthClick(row.querySelector('.rc-call-quick-text')); },
                    },
                    {
                        label: 'Mark Read', icon: 'tabler-flag',
                        enabled: row.getAttribute('data-rc-read') !== '1',
                        run: function () {
                            var id = historyId(row);
                            if (!id) return;

                            sendJson(list.getAttribute('data-awt-gestures-read-url'), 'PATCH', { ids: [Number(id)] })
                                .then(function () {
                                    row.setAttribute('data-rc-read', '1');
                                    row.classList.add('rc-call-item-read');
                                    refreshCounters();
                                })
                                .catch(function () { toastError('Could not mark the call as read.'); });
                        },
                    },
                    {
                        label: 'Copy Number', icon: 'tabler-copy',
                        run: function () { copyToClipboard(row.getAttribute('data-rc-number') || ''); },
                    },
                    {
                        label: 'Clear Entry', icon: 'tabler-trash', tone: 'danger',
                        run: function () { MODULES.history.left.run(row, list); },
                    },
                ];
            },
        },
    };

    function resolve(value, row) {
        return typeof value === 'function' ? value(row) : value;
    }

    // ── Action zones ────────────────────────────────────────────────────────

    /**
     * Build the two coloured zones for a row, once per gesture.
     *
     * They are parked immediately outside the row's edges, so the row's own
     * transform is the entire reveal animation — no second element to keep in
     * sync, and nothing to clean up if a frame is dropped.
     */
    function ensureZones(row, module) {
        var zones = { left: row.querySelector('.awt-mg-zone-start'), right: row.querySelector('.awt-mg-zone-end') };
        if (zones.left && zones.right) return zones;

        zones.left = buildZone('awt-mg-zone-start', module.right, row);   // revealed by a RIGHT swipe
        zones.right = buildZone('awt-mg-zone-end', module.left, row);     // revealed by a LEFT swipe
        row.appendChild(zones.left);
        row.appendChild(zones.right);

        return zones;
    }

    function buildZone(cls, action, row) {
        var zone = document.createElement('div');
        zone.className = 'awt-mg-zone ' + cls;
        zone.setAttribute('aria-hidden', 'true');
        // Marked ignorable so awt-master-detail.js never mistakes a tap that
        // lands here for a tap on the row.
        zone.setAttribute('data-awt-md-ignore', '');

        var icon = document.createElement('i');
        icon.className = 'icon-base ti ' + resolve(action.icon, row);
        var label = document.createElement('span');
        label.className = 'awt-mg-zone-label';
        label.textContent = resolve(action.label, row);

        zone.appendChild(icon);
        zone.appendChild(label);

        return zone;
    }

    function clearZones(row) {
        row.querySelectorAll('.awt-mg-zone').forEach(function (z) { z.remove(); });
    }

    function slideRow(row, x) {
        row.style.transform = 'translate3d(' + x + 'px, 0, 0)';
    }

    /** Ease the row home and take the scaffolding back out of the DOM. */
    function settleRow(row) {
        row.classList.add('awt-mg-settling');
        row.classList.remove('awt-mg-dragging', 'awt-mg-armed-start', 'awt-mg-armed-end');
        row.style.transform = '';

        window.setTimeout(function () {
            row.classList.remove('awt-mg-settling');
            clearZones(row);
        }, 220);
    }

    /**
     * Rubber band: the row tracks the finger one-for-one to the commit point,
     * then gives progressively less, so it always feels attached but can never
     * be dragged off the screen.
     */
    function damp(dx) {
        var sign = dx < 0 ? -1 : 1;
        var mag = Math.abs(dx);
        if (mag <= COMMIT_PX) return dx;

        return sign * Math.min(MAX_PULL_PX, COMMIT_PX + (mag - COMMIT_PX) * 0.35);
    }

    // ── Ghost-click suppression ─────────────────────────────────────────────

    /**
     * Eat the one click a completed touch sequence leaves behind.
     *
     * Without this, a swipe or a long press would ALSO register as a row tap
     * and awt-master-detail.js would slide the detail sheet up over the action
     * we just took. Capture phase so it lands before any delegated handler;
     * self-removing on the first click or shortly after, so a genuine tap that
     * follows is never swallowed. Clicks we dispatch ourselves are let through.
     */
    function suppressGhostClick() {
        var done = false;

        function release() {
            if (done) return;
            done = true;
            document.removeEventListener('click', kill, true);
        }

        function kill(e) {
            if (e.awtGestureSynthetic) return;
            e.stopPropagation();
            e.preventDefault();
            release();
        }

        document.addEventListener('click', kill, true);
        window.setTimeout(release, GHOST_CLICK_MS);
    }

    // ── The floating action sheet ───────────────────────────────────────────

    var sheet = null;

    function buildSheet() {
        if (sheet) return sheet;

        var root = document.createElement('div');
        root.id = 'mobileActionSheet';
        root.className = 'awt-mg-sheet';
        root.hidden = true;
        root.innerHTML =
            '<div class="awt-mg-sheet-backdrop" data-awt-mg-dismiss></div>' +
            '<div class="awt-mg-sheet-panel" role="dialog" aria-modal="true" aria-label="Quick actions">' +
                '<div class="awt-mg-sheet-grabber" aria-hidden="true"></div>' +
                '<div class="awt-mg-sheet-title"></div>' +
                '<div class="awt-mg-sheet-items"></div>' +
                '<button type="button" class="awt-mg-sheet-cancel" data-awt-mg-dismiss>Cancel</button>' +
            '</div>';

        document.body.appendChild(root);
        sheet = root;
        wireSheet(root);

        return root;
    }

    /**
     * Did we push a history entry for the open sheet?
     *
     * Only when awt-master-detail.js does NOT have a pane open. Both files
     * listen for popstate, and pushing a second entry while its sheet is up
     * would make one Back press unwind two things at once.
     */
    var sheetPushedHistory = false;

    function openSheet(row, list, module) {
        var items = (module.sheet ? module.sheet(row, list) : []).filter(function (item) {
            return item.enabled !== false;
        });
        if (!items.length) return;

        paintSheet(sheetTitleFor(row, module), items, row, list);
    }

    /**
     * Render and raise the sheet for an already-resolved item list.
     *
     * Split out of openSheet so callers that are not row-and-list shaped can
     * use the same sheet — the message bubbles on /text long-press into this,
     * and a second implementation of a bottom sheet (backdrop, grabber,
     * drag-to-dismiss, Back-button unwind, dark mode) is exactly what this
     * file exists to avoid. `row` and `list` are passed through to the item
     * callbacks untouched, and are simply undefined for those callers.
     */
    function paintSheet(title, items, row, list) {
        var root = buildSheet();

        var body = root.querySelector('.awt-mg-sheet-items');
        body.innerHTML = '';
        items.forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'awt-mg-sheet-item' + (item.tone === 'danger' ? ' is-danger' : '');
            btn.innerHTML = '<i class="icon-base ti ' + resolve(item.icon, row) + '"></i>' +
                '<span></span>';
            btn.querySelector('span').textContent = resolve(item.label, row);
            btn.addEventListener('click', function () {
                closeSheet();
                // Let the sheet finish leaving before the action redraws the
                // list underneath it.
                window.setTimeout(function () { item.run(row, list); }, 160);
            });
            body.appendChild(btn);
        });

        root.querySelector('.awt-mg-sheet-title').textContent = title || '';

        root.hidden = false;
        // Next frame, so the transition has a starting state to animate from.
        window.requestAnimationFrame(function () { root.classList.add('is-open'); });
        document.body.classList.add('awt-mg-sheet-open');

        sheetPushedHistory = false;
        if (!document.querySelector('[data-awt-md].awt-md-detail-active')) {
            try {
                window.history.pushState({ awtMgSheet: true }, '');
                sheetPushedHistory = true;
            } catch (e) { /* history unavailable — the other dismissals still work */ }
        }
    }

    function sheetTitleFor(row, module) {
        var name = row.getAttribute('data-vm-name')
            || row.getAttribute('data-rc-name')
            || (row.querySelector('.rc-conv-name') || {}).textContent
            || rowNumber(row);

        return (name || '').trim() || ('Quick actions for this ' + module.label);
    }

    function closeSheet() {
        if (!sheet || sheet.hidden) return;

        sheet.classList.remove('is-open');
        document.body.classList.remove('awt-mg-sheet-open');
        window.setTimeout(function () {
            if (sheet && !sheet.classList.contains('is-open')) sheet.hidden = true;
        }, 220);

        if (sheetPushedHistory) {
            sheetPushedHistory = false;
            try { window.history.back(); } catch (e) { /* nothing to unwind */ }
        }
    }

    function wireSheet(root) {
        // Tap outside, or Cancel.
        root.addEventListener('click', function (e) {
            if (e.target.closest('[data-awt-mg-dismiss]')) closeSheet();
        });

        // Drag the panel down to dismiss.
        var panel = root.querySelector('.awt-mg-sheet-panel');
        var startY = 0;
        var dragging = false;

        panel.addEventListener('touchstart', function (e) {
            if (e.touches.length !== 1) return;
            startY = e.touches[0].clientY;
            dragging = true;
            panel.classList.add('is-dragging');
        }, { passive: true });

        panel.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            var dy = e.touches[0].clientY - startY;
            if (dy <= 0) { panel.style.transform = ''; return; }
            panel.style.transform = 'translate3d(0, ' + dy + 'px, 0)';
        }, { passive: true });

        panel.addEventListener('touchend', function (e) {
            if (!dragging) return;
            dragging = false;
            panel.classList.remove('is-dragging');
            var dy = (e.changedTouches[0] || {}).clientY - startY;
            panel.style.transform = '';
            if (dy > 90) closeSheet();
        });
    }

    // Hardware / browser Back closes the sheet rather than leaving the page.
    window.addEventListener('popstate', function () {
        if (sheet && !sheet.hidden) {
            sheetPushedHistory = false;   // this popstate IS the unwind
            closeSheet();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSheet();
    });

    // ── The gesture itself ──────────────────────────────────────────────────

    /** Live state for the one touch we are tracking, or null. */
    var g = null;

    function endGesture(settle) {
        if (!g) return;

        window.clearTimeout(g.timer);
        if (settle && g.row) settleRow(g.row);
        g = null;
    }

    document.addEventListener('touchstart', function (e) {
        // Something else is mid-gesture, or this is a pinch — drop everything.
        if (g) { endGesture(true); }
        if (!gesturesApply()) return;
        if (e.touches.length !== 1) return;

        var target = e.target;
        if (!target || !target.closest) return;

        var row = target.closest('[data-mobile-gesture="true"]');
        if (!row) return;

        // A tap aimed at one of the row's own controls belongs to that control.
        var control = target.closest(CONTROL_SELECTOR);
        if (control && control !== row && row.contains(control)) return;

        var list = row.closest('[data-awt-gestures]');
        var module = list ? MODULES[list.getAttribute('data-awt-gestures')] : null;
        if (!module) return;

        // A list in multi-select mode is being operated with checkboxes; a
        // swipe there would fight the selection the agent is building.
        if (list.classList.contains('rc-edit-mode')) return;

        var touch = e.touches[0];
        g = {
            row: row,
            list: list,
            module: module,
            startX: touch.clientX,
            startY: touch.clientY,
            dx: 0,
            axis: null,          // null → undecided, 'x' → ours, 'y' → the page's
            longPressed: false,
            timer: window.setTimeout(function () {
                if (!g || g.axis === 'x') return;

                g.longPressed = true;
                g.axis = 'y';    // stop this touch from also becoming a swipe
                if (navigator.vibrate) { try { navigator.vibrate(50); } catch (err) { /* opt-in only */ } }
                row.classList.add('awt-mg-pressed');
                openSheet(row, list, module);
            }, LONG_PRESS_MS),
        };
    }, { passive: true });

    // Not passive: once the drag is decisively horizontal we must be able to
    // preventDefault. On a vertical drag we return before ever calling it, so
    // scrolling keeps the browser's own fast path.
    document.addEventListener('touchmove', function (e) {
        if (!g) return;
        if (e.touches.length !== 1) { endGesture(true); return; }

        var touch = e.touches[0];
        var dx = touch.clientX - g.startX;
        var dy = touch.clientY - g.startY;

        if (g.axis === null) {
            if (Math.abs(dx) < AXIS_SLOP_PX && Math.abs(dy) < AXIS_SLOP_PX) return;

            window.clearTimeout(g.timer);

            // Ties go to the page. A list that will not scroll is worse than a
            // list without gestures.
            if (Math.abs(dy) >= Math.abs(dx)) {
                g.axis = 'y';
                g.row.classList.remove('awt-mg-pressed');

                return;
            }

            g.axis = 'x';
            g.zones = ensureZones(g.row, g.module);
            g.row.classList.add('awt-mg-dragging');
            g.row.classList.remove('awt-mg-pressed');
        }

        if (g.axis !== 'x') return;

        if (e.cancelable) e.preventDefault();
        g.dx = damp(dx);
        slideRow(g.row, g.dx);

        var armed = Math.abs(dx) >= COMMIT_PX;
        g.row.classList.toggle('awt-mg-armed-start', armed && dx > 0);
        g.row.classList.toggle('awt-mg-armed-end', armed && dx < 0);
    }, { passive: false });

    document.addEventListener('touchend', function () {
        if (!g) return;

        var row = g.row;
        var list = g.list;
        var module = g.module;
        var dx = g.dx;
        var wasSwipe = g.axis === 'x';
        var wasPress = g.longPressed;

        row.classList.remove('awt-mg-pressed');
        endGesture(wasSwipe);

        if (wasPress) {
            suppressGhostClick();

            return;
        }

        if (!wasSwipe) return;

        // Any horizontal drag ate the tap, committed or not.
        suppressGhostClick();

        if (Math.abs(dx) < COMMIT_PX) return;

        var action = dx > 0 ? module.right : module.left;
        if (action && typeof action.run === 'function') action.run(row, list);
    }, { passive: true });

    document.addEventListener('touchcancel', function () { endGesture(true); }, { passive: true });

    // A long press on a phone otherwise raises the OS text-selection menu over
    // our sheet. Only suppressed while the sheet is actually up.
    document.addEventListener('contextmenu', function (e) {
        if (sheet && !sheet.hidden) e.preventDefault();
    });

    // ── Public surface, for the tests and for anything that adds a list ─────

    window.AwtMobileGestures = {
        MEDIA_QUERY: MEDIA_QUERY,
        LONG_PRESS_MS: LONG_PRESS_MS,
        COMMIT_PX: COMMIT_PX,
        AXIS_SLOP_PX: AXIS_SLOP_PX,
        modules: MODULES,
        applies: gesturesApply,
        closeSheet: closeSheet,
        /**
         * Raise the sheet for a caller that owns its own gesture.
         *
         * openSheet() is for registered list rows; this is for anything else
         * that has already decided a sheet should appear — /text long-presses
         * a message bubble into it. Items are {icon, label, tone?, run()}.
         *
         * The sheet's stylesheet is entirely inside @media (max-width: 768px),
         * so callers must confine this to phones and give desktop its own
         * surface.
         */
        openCustomSheet: function (opts) {
            opts = opts || {};
            var items = (opts.items || []).filter(function (i) { return i && i.enabled !== false; });
            if (!items.length) return false;
            paintSheet(opts.title || '', items);
            // The sheet appears WHILE the finger is still down, so the click
            // the browser emits at touchend lands on the backdrop that just
            // slid under it and dismisses the sheet before it can be read.
            // Callers raising this from their own long press get the same
            // suppression the row gestures use.
            suppressGhostClick();
            return true;
        },
        /** Eat the one synthetic click a completed touch sequence leaves. */
        suppressGhostClick: suppressGhostClick,
        isSheetOpen: function () { return !!(sheet && !sheet.hidden); },
    };
})();
