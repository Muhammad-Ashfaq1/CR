/* ============================================================================
 * awt-master-detail.js — shared mobile single-pane toggle for rc-* layouts
 * ----------------------------------------------------------------------------
 * The two/three-pane "list + detail" screens (SMS, calls, contacts, fax, video
 * hub) render side-by-side on desktop. Below md (≤767.98px) awt-responsive.css
 * collapses them to a single pane; this script flips WHICH pane is shown:
 *   • tapping a list row  → show the detail pane   (add `.awt-md-detail-active`)
 *   • tapping a back arrow → show the list again   (remove the class)
 *
 * It is deliberately generic and OPT-IN via three markers a page adds to its
 * EXISTING containers (no new nesting, no per-row edits):
 *   [data-awt-md]         on the flex body wrapping the two panes
 *   [data-awt-md-list]    on the list pane
 *   [data-awt-md-detail]  on the detail pane
 *   [data-awt-md-back]    on a back button placed inside the detail pane
 *
 * On a narrow screen the detail opens as a BOTTOM SHEET covering 60% of the
 * viewport, with the list still visible (and dimmed) behind it — you can see
 * where you were, and dismissing lands back on it.
 *
 * A row tap opens the detail directly — no second tap on a HUD/details icon.
 * Opening pushes one history entry on mobile, so the browser/hardware Back
 * button returns to the list (with its tab, search, filters, pagination and
 * scroll position all intact, because the list is never re-rendered).
 *
 * Non-regression: this only toggles ONE class on the body wrapper. It never
 * touches the existing row-click handlers (they still fire and load data as
 * before — we run alongside them), reads no dimensions, and on desktop the
 * class has no visual effect because the collapsing CSS lives inside the media
 * query — and no history entry is pushed there either. Delegated on document,
 * bound once, so Turbo navigations never double-bind.
 * ==========================================================================*/
(function () {
    'use strict';

    if (window.__awtMasterDetailBound) return;
    window.__awtMasterDetailBound = true;

    var ACTIVE = 'awt-md-detail-active';

    // Row types across the rc-* families, plus an explicit opt-in hook.
    // `.rc-fav-item` = Phone favourites, `.adl-item` = Auto-Dialer sessions.
    var ITEM_SELECTOR = '.rc-conv-item, .rc-call-item, .rc-contact-item, .rc-contacts-fav-row, ' +
        '.rc-fav-item, .adl-item, ' +
        // AI call notes (Phone → Notes). Its rows are their own type rather
        // than .rc-call-item, so they were never recognised as rows at all —
        // tapping one on a phone rendered the note into a detail pane that
        // stayed off screen, and the tab looked like it did nothing.
        //
        // Listed HERE rather than marked with data-awt-md-open: that hook
        // opens the detail from anywhere it is clicked, and these rows carry a
        // selection checkbox. As a row type it goes through the list handler
        // below, which leaves a tap on a nested control to that control.
        '.awt-ai-note-row, ' +
        // Video meetings (Upcoming and Past) are one row type and mark
        // themselves with the master-detail hook rather than a styling class.
        '[data-video-card], [data-awt-md-open]';

    /**
     * Controls that DO something on their own.
     *
     * A tap on a row's Call, Text, Play, Delete or Favourite button must run
     * that action and nothing else — opening the detail as well would yank the
     * agent away from the list they were working through. The row itself is
     * allowed to be one of these (some lists render rows as buttons/links);
     * only a control NESTED inside the row suppresses the navigation.
     */
    var ACTION_SELECTOR = 'button, a[href], input, select, textarea, label, [role="button"], [data-awt-md-ignore]';

    function listPaneOf(root) {
        return root ? root.querySelector('[data-awt-md-list]') : null;
    }

    function detailPaneOf(root) {
        return root ? root.querySelector('[data-awt-md-detail]') : null;
    }

    /**
     * Has the detail taken over the screen?
     *
     * Asked of the computed style rather than of a breakpoint, because the
     * collapse point differs per module — the SMS/Contacts/Video panes split at
     * 768px, the Phone family at 992px. Two shapes count:
     *
     *   • a bottom sheet — `position: fixed`, set by exactly the rules that
     *     make it a sheet (the list stays visible behind it, so its visibility
     *     cannot be the signal)
     *   • a full-screen detail — the Text tab's chat room, where the list is
     *     swapped out entirely
     *
     * On desktop neither is true, so no history entry is pushed and Back keeps
     * meaning what it always did there.
     */
    function isSheet(root) {
        var dp = detailPaneOf(root);
        if (!dp || !window.getComputedStyle) return false;

        if (window.getComputedStyle(dp).position === 'fixed') return true;

        // Full-screen mode: the list is gone, so the detail is the screen.
        var lp = listPaneOf(root);

        return !!lp && window.getComputedStyle(lp).display === 'none';
    }

    /**
     * When the detail last closed.
     *
     * A tap that dismisses the sheet lands near the top of it — which is
     * exactly where the LIST is once the sheet slides away. Touch browsers can
     * deliver a second, synthetic click at those coordinates after the first
     * one has already closed the sheet, and that ghost tap would land on a row
     * and reopen it: the "closes for a moment, then opens again" behaviour.
     * Opens are ignored for a moment after a close, which is far shorter than
     * any deliberate tap-close-tap and long enough to swallow the ghost.
     *
     * Recorded PER ROOT: dismissing the Phone sheet has no business blocking a
     * tap in Contacts.
     */
    var REOPEN_GUARD_MS = 400;

    function closedRecently(root) {
        var at = parseInt(root.dataset.awtMdClosedAt || '0', 10);

        return at > 0 && (Date.now() - at) < REOPEN_GUARD_MS;
    }

    /** How long to wait for popstate before closing without it. */
    var HISTORY_FALLBACK_MS = 150;

    /**
     * Lift Turbo's marker off the entry the sheet is about to bury, and hand
     * it to the caller to put on the entry the sheet pushes.
     *
     * These pages run Turbo Drive. Turbo watches popstate, and when the entry
     * it lands on carries its own `state.turbo` it reads the pop as a
     * NAVIGATION and answers with a restoration visit: the body is replaced by
     * a cached snapshot and every page script runs again. So closing the sheet
     * — a history.back() over the entry we pushed to open it — reloaded the
     * whole screen underneath. On the Phone page that showed twice over: the
     * re-run scripts read the row still named in the address bar (?call=42,
     * written by calls-deeplink.js) and opened it again. Close, flash, open.
     *
     * An entry with no marker takes Turbo's other branch — re-stamp the entry,
     * cache a snapshot, no visit, no swap, no re-run. Which is the truth of a
     * dismissed sheet: the page under it never went anywhere.
     *
     * The marker MOVES rather than being dropped, because the two entries sit
     * at the same URL and the pushed one is the newer face of that page. Left
     * markerless (as it was), it swallowed a real return: open a call, tap
     * Message, and Back from /text landed on the sheet's entry with Turbo
     * declining to render — the Text page's markup under the Phone page's
     * address. Carrying the marker up makes that Back a proper restore, while
     * the entry a close pops to stays quiet.
     *
     * The URL is untouched either way, so the deep link still addresses the
     * open row and is still shareable, and other state keys ride along. Where
     * Turbo is not running there is no marker and this does nothing at all.
     */
    function takeTurboMarker() {
        var state = window.history.state;
        if (!state || !state.turbo) return null;

        var kept = {};
        Object.keys(state).forEach(function (key) {
            if (key !== 'turbo') kept[key] = state[key];
        });

        window.history.replaceState(kept, '');

        return state.turbo;
    }

    function showDetail(root, viaHistory) {
        if (!root || root.classList.contains(ACTIVE)) return;
        if (!viaHistory && closedRecently(root)) return;

        // ONE sheet at a time.
        //
        // The long-press action sheet (awt-mobile-view-gestures.js) is a second,
        // independent overlay over the same rows. A press that ends as a tap —
        // or the synthetic click a touch sequence leaves behind — could open the
        // detail on top of it, so both were on screen at once: two stacked
        // sheets, each with its own dismissal, which is the "double view".
        //
        // The long press got there first and is the more deliberate gesture, so
        // it wins. Its own dismissal puts the row back within reach.
        if (!viaHistory
            && window.AwtMobileGestures
            && typeof window.AwtMobileGestures.isSheetOpen === 'function'
            && window.AwtMobileGestures.isSheetOpen()) {
            return;
        }

        // Whatever the last sheet grew to is not this one's business.
        root.style.removeProperty('--awt-md-sheet-grown');

        // Preserve the list scroll position so returning lands where you were.
        var lp = listPaneOf(root);
        if (lp) root.dataset.awtMdScroll = String(lp.scrollTop || 0);
        root.classList.add(ACTIVE);

        // Make the hardware / browser Back button close the detail instead of
        // leaving the page — the list, its tab, search, filters and scroll are
        // all still in the DOM, so returning to them costs nothing and loses
        // nothing. Only where the detail actually took over the screen.
        if (!viaHistory && isSheet(root)) {
            try {
                var state = { awtMdDetail: true };
                var turbo = takeTurboMarker();
                if (turbo) state.turbo = turbo;
                window.history.pushState(state, '');
                root.dataset.awtMdHistory = '1';
            } catch (e) { /* history unavailable — the back control still works */ }
        }

        // Move focus into the detail pane for keyboard / screen-reader users.
        var back = root.querySelector('[data-awt-md-back]');
        if (back) back.focus();
    }

    /**
     * Actually close. The ONLY place the active class is removed.
     *
     * Having one exit is the point: when the control removed the class itself
     * AND asked the browser to go back, the state could be written twice for a
     * single dismissal, and anything that re-entered in between (a ghost tap, a
     * restored history entry) left the sheet open again.
     */
    function closeNow(root) {
        if (!root || !root.classList.contains(ACTIVE)) return;

        root.classList.remove(ACTIVE);
        // The extra height it grew to is NOT given back here. A sheet that
        // shrank on its way out flinched: the top edge dropped a third of a
        // screen on the first frame of the dismissal, and then slid. It leaves
        // the size it was, and showDetail resets it on the way back in — so the
        // next row you tap still opens at the normal height.
        root.dataset.awtMdClosedAt = String(Date.now());

        // Restore the remembered list scroll position.
        var lp = listPaneOf(root);
        if (lp && root.dataset.awtMdScroll != null) {
            lp.scrollTop = parseInt(root.dataset.awtMdScroll, 10) || 0;
        }

        delete root.dataset.awtMdHistory;

        // Tell the page its detail is gone.
        //
        // This layer knows the pane closed; only the page knows what was in it
        // — a playing voicemail, a selected row, a deep link in the address
        // bar. Announcing it once, from the single exit, means a page hooks
        // EVERY dismissal (the close button, the dimmed backdrop, a pull down
        // on the grip, the hardware Back button) with one listener instead of
        // guessing at each of them.
        document.dispatchEvent(new CustomEvent('awt:md-detail-closed', {
            detail: { root: root },
        }));
    }

    /**
     * Ask to close — from the back control, or a tap on the backdrop.
     *
     * When the open pushed a history entry, this goes BACK and lets the popstate
     * handler do the closing, so the entry is unwound and the class is removed
     * by one path rather than two. A safety timer covers the case where popstate
     * never arrives, so a dismissal can never be swallowed.
     */
    function requestClose(root) {
        if (!root || !root.classList.contains(ACTIVE)) return;

        if (root.dataset.awtMdHistory === '1') {
            root.dataset.awtMdHistory = 'closing';
            try {
                window.history.back();
                window.setTimeout(function () { closeNow(root); }, HISTORY_FALLBACK_MS);

                return;
            } catch (e) {
                root.dataset.awtMdHistory = '1';
            }
        }

        closeNow(root);
    }

    // Kept as the public name the rest of the file (and any caller) uses.
    function showList(root, viaHistory) {
        if (viaHistory) {
            closeNow(root);

            return;
        }

        requestClose(root);
    }

    // Browser / hardware Back while a detail is open → back to the list.
    // Browser / hardware Back, and the entry unwound by requestClose(), both
    // land here — the single close path.
    window.addEventListener('popstate', function () {
        var open = document.querySelector('[data-awt-md].' + ACTIVE);
        if (open) closeNow(open);
    });

    document.addEventListener('click', function (e) {
        // Back arrow → return to the list.
        var back = e.target.closest('[data-awt-md-back]');
        if (back) {
            showList(back.closest('[data-awt-md]'));
            return;
        }

        // The dimmed area above the sheet is the ROOT's own ::after, so a tap on
        // it lands on the root element rather than on any child. Dismiss, as a
        // bottom sheet should.
        if (e.target.hasAttribute && e.target.hasAttribute('data-awt-md')) {
            if (e.target.classList.contains(ACTIVE) && isSheet(e.target)) {
                showList(e.target);
                return;
            }
        }

        // Explicit "open the secondary pane" trigger (e.g. the Phone HUD button)
        // works regardless of where it sits.
        var opener = e.target.closest('[data-awt-md-open]');
        if (opener) {
            showDetail(opener.closest('[data-awt-md]'));
            return;
        }

        var listPane = e.target.closest('[data-awt-md-list]');
        if (!listPane) return;

        var root = listPane.closest('[data-awt-md]');
        // In "manual" mode generic row taps must NOT open the secondary pane.
        // Retained for any pane that is genuinely an on-demand surface rather
        // than the detail of the row you tapped.
        if (root && root.hasAttribute('data-awt-md-manual')) return;

        var item = e.target.closest(ITEM_SELECTOR);
        if (!item || !listPane.contains(item)) return;

        // A row's own action button (Call, Text, Play, Delete, Favourite, the
        // select checkbox, a dropdown toggle) does its job WITHOUT navigating.
        // The row may itself be a button or link, so only a nested control
        // counts.
        var action = e.target.closest(ACTION_SELECTOR);
        if (action && action !== item && item.contains(action)) return;

        showDetail(root);
    }, false);

    /* ── How tall the sheet is ─────────────────────────────────────────────
     *
     * ONE mechanism: `--awt-md-sheet-grown`, a pixel figure on the root that
     * the stylesheet adds to the short detent, capped there by `max-height` at
     * --awt-md-sheet-h-max (90% of the viewport). The grip drives it to the cap
     * or back to zero; reading drives it to the cap too.
     *
     * The script animates it frame by frame instead of handing the job to a CSS
     * transition, and the reason is the whole difficulty of a bottom sheet: it
     * is anchored to the BOTTOM of the screen, so every pixel of new height
     * arrives at the top and shoves the content down-screen — a third of a
     * screen of lurch, right where someone is reading. Growing by hand means
     * the scroll position can give back exactly what the height takes ON THE
     * SAME FRAME, so the words under the thumb do not move at all while the
     * sheet opens up around them.
     *
     * A CSS transition could not do that half. It would also animate every
     * `dvh` change a phone makes as its address bar slides away, which is what
     * had the sheet wobbling through every scroll.
     */
    var GROW_MS = 300;

    var growFrame = null;

    function grownPx(root) {
        return parseFloat(root.style.getPropertyValue('--awt-md-sheet-grown')) || 0;
    }

    /** Taller than the short detent? */
    function isTall(root) {
        return grownPx(root) > 0;
    }

    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    /**
     * How much taller the sheet is still allowed to get, in pixels.
     *
     * Asked of the layout rather than computed from the units, because only the
     * browser knows what 90% of a `dvh` viewport is this second, and only it
     * knows where `max-height` stopped the last request.
     */
    function headroom(root, pane) {
        var now = pane.getBoundingClientRect().height;
        var saved = root.style.getPropertyValue('--awt-md-sheet-grown');

        root.style.setProperty('--awt-md-sheet-grown', '200vh');   // far past the cap
        var most = pane.getBoundingClientRect().height;

        if (saved) root.style.setProperty('--awt-md-sheet-grown', saved);
        else root.style.removeProperty('--awt-md-sheet-grown');

        return most - now;
    }

    /* Decelerating, to match --awt-md-sheet-ease-in. */
    function easeOut(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    /**
     * Change the height by `delta` pixels, holding the content still.
     *
     * Negative shrinks. `scroller` is the element that actually scrolls inside
     * the sheet, and it is what makes this honest: on every frame it gives up
     * exactly the pixels the sheet just gained, so the two cancel out. When it
     * runs out — a short detail scrolled to its top — the content does slide,
     * but it slides smoothly with the growth instead of jumping.
     */
    function resizeSheet(root, scroller, delta) {
        if (window.cancelAnimationFrame) window.cancelAnimationFrame(growFrame);
        if (!delta) return;

        var startGrown = grownPx(root);
        var startScroll = scroller ? scroller.scrollTop : 0;

        var apply = function (progress) {
            var gained = delta * progress;
            root.style.setProperty('--awt-md-sheet-grown', (startGrown + gained) + 'px');
            if (scroller) scroller.scrollTop = Math.max(0, startScroll - gained);
        };

        if (prefersReducedMotion() || !window.requestAnimationFrame) {
            apply(1);

            return;
        }

        var began = null;
        growFrame = window.requestAnimationFrame(function step(now) {
            if (began === null) began = now;

            var progress = Math.min(1, (now - began) / GROW_MS);
            apply(easeOut(progress));

            if (progress < 1) growFrame = window.requestAnimationFrame(step);
        });
    }

    /** All the way up. */
    function growSheet(root, scroller) {
        var pane = detailPaneOf(root);
        if (!pane) return;

        resizeSheet(root, scroller, headroom(root, pane));
    }

    /** All the way back down. */
    function shrinkSheet(root, scroller) {
        resizeSheet(root, scroller, -grownPx(root));
    }

    /* ── Drag the grip to resize the sheet ─────────────────────────────────
     *
     * Up  → the sheet grows to 90% of the viewport (--awt-md-sheet-h-max), for
     *       a detail with more in it than 60% of a phone can show.
     * Down→ back to the normal height, and again to dismiss.
     *
     * Deliberately SNAP-ON-RELEASE rather than following the finger. The sheet
     * is itself a scroll container, and a live drag has to fight that scroll
     * for every touchmove — which is how a sheet ends up half-dragged and
     * stuck. Reading the gesture once, at the end, cannot conflict with it.
     *
     * Listeners are passive: nothing here calls preventDefault, so the grip
     * never blocks the scrolling underneath it.
     */
    var DRAG_MIN = 40;   // px before a touch counts as a drag at all
    var dragY = null;
    var dragRoot = null;

    document.addEventListener('touchstart', function (e) {
        var handle = e.target.closest && e.target.closest('[data-awt-md-handle]');
        if (!handle || !e.touches || e.touches.length !== 1) return;

        var root = handle.closest('[data-awt-md]');
        // Only while the sheet is actually up — on desktop the same markup is
        // an ordinary header.
        if (!root || !root.classList.contains(ACTIVE) || !isSheet(root)) return;

        dragRoot = root;
        dragY = e.touches[0].clientY;
    }, { passive: true });

    document.addEventListener('touchend', function (e) {
        if (dragRoot === null || dragY === null) return;

        var root = dragRoot;
        var startY = dragY;
        dragRoot = null;
        dragY = null;

        var touch = (e.changedTouches && e.changedTouches[0]) || null;
        if (!touch) return;

        var dy = touch.clientY - startY;
        if (Math.abs(dy) < DRAG_MIN) return;   // a tap, or a twitch

        var pane = detailPaneOf(root);
        var scroller = pane ? scrollerIn(pane, pane) : null;

        if (dy < 0) {
            growSheet(root, scroller);

            return;
        }

        // Downward: give back the height first, and only dismiss from the
        // normal size — so one long pull cannot skip a step and close a sheet
        // the reader had just made bigger to read. That includes a sheet that
        // grew while being read: it is just as tall, however it got there.
        if (isTall(root)) {
            shrinkSheet(root, scroller);

            return;
        }

        showList(root);
    }, { passive: true });

    document.addEventListener('touchcancel', function () {
        dragRoot = null;
        dragY = null;
    }, { passive: true });

    /* ── Reading grows the sheet, without moving what is being read ───────
     *
     * The grip is the deliberate way to the tall sheet and it is easy to miss,
     * so scrolling is the other way in: read past the fold and the sheet takes
     * the whole 90% it is allowed. Two rules keep that from being the mess it
     * was on the first two attempts.
     *
     * IT WAITS FOR THE SCROLL TO STOP. The sheet is the scroll container, so
     * resizing it mid-scroll moves the content under the thumb and fires more
     * scroll events. Nothing happens until there are no scroll events for
     * SCROLL_SETTLE_MS, by which point momentum is over and the finger is gone.
     *
     * IT HOLDS THE CONTENT STILL. resizeSheet gives the scroll position back
     * exactly what the height takes, frame by frame, so the line being read
     * stays where it is while the sheet opens up around it.
     *
     * There is no shrink to match: undoing it would shove the page back down
     * for no reason anyone asked for. The grip takes the height back, and the
     * pull below takes the sheet away.
     */
    var SCROLL_SETTLE_MS = 150;
    var settleTimer = null;

    /**
     * The element that actually scrolls inside the sheet.
     *
     * Usually the pane, but a page is free to put the scrolling on a region
     * within it, and several do. Reading `pane.scrollTop` in that case gives a
     * permanent 0: the sheet would never grow, and the pull-to-dismiss below
     * would read every scroll as a pull from the top of the content and throw
     * the sheet away under someone who was only reading.
     */
    function scrollerIn(pane, from) {
        for (var el = from; el && el !== pane; el = el.parentElement) {
            if (el.scrollHeight - el.clientHeight > 1) {
                var overflowY = window.getComputedStyle(el).overflowY;
                if (overflowY === 'auto' || overflowY === 'scroll') return el;
            }
        }

        return pane;
    }

    document.addEventListener('scroll', function (e) {
        var scroller = e.target;
        if (!scroller || scroller.nodeType !== 1 || !scroller.closest) return;

        var pane = scroller.closest('[data-awt-md-detail]');
        if (!pane) return;

        var root = pane.closest('[data-awt-md]');
        if (!root || !root.classList.contains(ACTIVE) || !isSheet(root)) return;
        if (scroller.scrollTop <= 0) return;          // still at the top: nothing read yet

        window.clearTimeout(settleTimer);
        settleTimer = window.setTimeout(function () {
            growSheet(root, scroller);
        }, SCROLL_SETTLE_MS);
    }, true);

    /* ── Keep pulling down at the top and the sheet leaves ─────────────────
     *
     * At the top of the content there is nothing left to scroll, so carrying on
     * downward there means the sheet itself. That is the gesture every phone
     * user already has in their hands, and until now the only ways out were a
     * button and the strip of dimmed list above the sheet.
     *
     * Read once on release, like the grip drag, and for the same reason: the
     * pane is a scroll container, and a handler that fights it for every
     * touchmove is how a sheet ends up half-dragged and stuck. Both ends of the
     * gesture are checked against scrollTop 0, so a flick that actually scrolled
     * the content is a scroll and nothing else.
     *
     * A touch that starts on the grip belongs to the grip: it has its own
     * two-step (shrink, then dismiss), and letting both run would collapse and
     * close in one pull — precisely the step-skipping that one guards against.
     */
    var PULL_CLOSE_PX = 90;
    var pullY = null;
    var pullScroller = null;
    var pullRoot = null;

    function endPull() {
        pullY = null;
        pullScroller = null;
        pullRoot = null;
    }

    document.addEventListener('touchstart', function (e) {
        if (!e.touches || e.touches.length !== 1) return;
        if (!e.target.closest) return;
        if (e.target.closest('[data-awt-md-handle]')) return;

        var pane = e.target.closest('[data-awt-md-detail]');
        if (!pane) return;

        var root = pane.closest('[data-awt-md]');
        if (!root || !root.classList.contains(ACTIVE) || !isSheet(root)) return;

        // Whatever is actually scrolling under this finger — the pane, or a
        // region inside it. Asking the pane when a region scrolls gives a
        // permanent 0, which would read every scroll as a pull and dismiss the
        // sheet out from under someone who was only reading.
        var scroller = scrollerIn(pane, e.target);
        // Mid-content: this is a scroll, and dismissing out from under it would
        // be indistinguishable from the page falling over.
        if (scroller.scrollTop > 0) return;

        pullScroller = scroller;
        pullRoot = root;
        pullY = e.touches[0].clientY;
    }, { passive: true });

    document.addEventListener('touchend', function (e) {
        if (pullY === null) return;

        var scroller = pullScroller;
        var root = pullRoot;
        var startY = pullY;
        endPull();

        var touch = (e.changedTouches && e.changedTouches[0]) || null;
        if (!touch) return;
        if (touch.clientY - startY < PULL_CLOSE_PX) return;
        // The content moved after all — that was a scroll wearing a pull's
        // clothes.
        if (scroller.scrollTop > 0) return;

        showList(root);
    }, { passive: true });

    document.addEventListener('touchcancel', endPull, { passive: true });

})();
