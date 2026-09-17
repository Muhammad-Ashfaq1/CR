/**
 * Priority-plus tab strips.
 *
 * A tab row is only as wide as the pane it sits in, and the pane's width is
 * not knowable when the page is written: the Phone strip has 360px in a
 * desktop split and most of the window on a tablet. The old answer was to fix
 * the decision in the markup — four tabs inline, the rest behind a chevron,
 * for everybody — so on a wide pane the row showed a menu button beside a
 * hand's width of empty space, and two of the six tabs stayed hidden inside
 * it for no reason.
 *
 * This decides at the only moment the answer is known: after layout. Every
 * tab is authored inline, in priority order. If they all fit, the chevron is
 * not drawn at all. If they do not, tabs move into the menu from the END —
 * the lowest-priority first — until what remains fits, so the menu holds
 * exactly what could not be shown and nothing else.
 *
 * The active tab never moves into the menu. An underline you cannot see is
 * the same as no underline, and the row would then claim you are nowhere.
 *
 * Markup contract (see the Phone strip for a worked example):
 *
 *   <div data-awt-tab-overflow>
 *     <ul data-awt-tab-overflow-strip> <li><a>…</a></li> … </ul>
 *     <div data-awt-tab-overflow-more class="is-empty">   ← the ⌄, hidden while empty
 *       <a data-bs-toggle="dropdown">⌄</a>
 *       <ul data-awt-tab-overflow-menu></ul>              ← starts empty; JS fills it
 *     </div>
 *   </div>
 *
 * The <li> elements are MOVED, never re-rendered: whatever a tab carries —
 * a Bootstrap tab trigger, a link to another page, event handlers, an id
 * something else looks up — survives the trip in both directions. Only the
 * two presentational classes are swapped (.nav-link ↔ .dropdown-item).
 *
 * Requires no jQuery. Idempotent: re-running attach() on the same container
 * re-uses the existing controller rather than stacking listeners.
 */
'use strict';

(function () {
    'use strict';

    /** Set on the container while the chevron needs its space reserved. */
    var RESERVE_CLASS = 'has-tab-overflow';
    /** Set on the ⌄ while nothing has overflowed into it. */
    var EMPTY_CLASS = 'is-empty';
    /**
     * The authored position of a tab, written into the DOM on first sight.
     *
     * "Authored order" cannot be re-read from the strip later, because by then
     * this module has moved things out of it — and it is re-read more often
     * than it looks: `turbo:load` fires on a FIRST load too, so a fresh page
     * scans twice. The second pass used to capture the trimmed row as gospel,
     * orphaning whatever the first pass had filed away and then hiding the
     * chevron on top of it. Stamping survives both a re-scan and a Turbo
     * snapshot restore, because it travels in the markup.
     */
    var ORDER_ATTR = 'data-awt-tab-index';

    var controllers = [];

    function childList(el) {
        return Array.prototype.slice.call(el.children);
    }

    function anchorOf(li) {
        return li.querySelector('a') || li.querySelector('button');
    }

    function isActive(li) {
        var a = anchorOf(li);
        return !!a && (a.classList.contains('active') || a.getAttribute('aria-selected') === 'true');
    }

    /**
     * Forget any Bootstrap Tab instance cached on this anchor.
     *
     * A Tab resolves its siblings ONCE, in its constructor, with
     * closest('.list-group, .nav, [role="tablist"]') — and it caches the
     * instance on the element. An anchor that has moved between the strip and
     * the menu has a different answer to that question than the one its
     * instance is holding, and a stale one either switches the wrong tab or
     * silently switches nothing. Dropping the instance costs one object; the
     * next show() rebuilds it against wherever the anchor now lives.
     */
    function forgetTabInstance(a) {
        if (!a || !window.bootstrap || !window.bootstrap.Tab) return;
        var inst = window.bootstrap.Tab.getInstance(a);
        if (!inst) return;
        try { inst.dispose(); } catch (e) { /* already gone */ }
    }

    function dressAsTab(li) {
        li.classList.add('nav-item');
        var a = anchorOf(li);
        if (!a) return;
        if (a.classList.contains('dropdown-item')) forgetTabInstance(a);
        a.classList.remove('dropdown-item');
        a.classList.add('nav-link');
    }

    function dressAsMenuItem(li) {
        li.classList.remove('nav-item');
        var a = anchorOf(li);
        if (!a) return;
        if (!a.classList.contains('dropdown-item')) forgetTabInstance(a);
        a.classList.remove('nav-link');
        a.classList.add('dropdown-item');
    }

    /**
     * Does the row not fit?
     *
     * 1px of slack because widths are fractional: a strip whose content is
     * 0.4px wider than its box is a rounding artefact, not an overflow, and
     * treating it as one would banish a tab to a menu to reclaim half a pixel.
     */
    function overflows(strip) {
        return strip.scrollWidth > strip.clientWidth + 1;
    }

    /**
     * Every tab this strip owns, in the order it was written.
     *
     * Reads BOTH halves — the row and the menu — because a controller is not
     * always the first to look at this DOM. On the first sight of a container
     * each tab is stamped with its position; from then on the order is that
     * stamp, not wherever the element happens to be sitting.
     *
     * The stamping pass walks the strip first and the menu second, which is the
     * authored order in every case the trimmer can produce: it moves tabs out
     * from the END, so the menu holds the tail of the row.
     */
    function captureOrder(strip, menu) {
        var inStrip = childList(strip);
        var inMenu = menu ? childList(menu) : [];
        var all = inStrip.concat(inMenu);

        all.forEach(function (li, i) {
            if (li.getAttribute(ORDER_ATTR) === null) li.setAttribute(ORDER_ATTR, String(i));
        });

        return all.sort(function (a, b) {
            return Number(a.getAttribute(ORDER_ATTR)) - Number(b.getAttribute(ORDER_ATTR));
        });
    }

    function Controller(container) {
        this.container = container;
        this.strip = container.querySelector('[data-awt-tab-overflow-strip]');
        this.more = container.querySelector('[data-awt-tab-overflow-more]');
        this.menu = container.querySelector('[data-awt-tab-overflow-menu]');
        // Authored order, from the stamps. Everything is put back in this order
        // before each measurement, so the row can never drift out of sequence
        // however many times it has been re-laid-out — or re-scanned.
        this.order = this.strip ? captureOrder(this.strip, this.menu) : [];
        this.frame = null;
    }

    Controller.prototype.usable = function () {
        return !!(this.strip && this.more && this.menu && this.order.length);
    };

    Controller.prototype.layout = function () {
        if (!this.usable()) return;

        var strip = this.strip;
        var menu = this.menu;

        // 1. Everyone home. appendChild MOVES a node, so this empties the menu
        //    as a side effect — the menu is never cleared by hand, which is
        //    what keeps the elements (and their listeners) alive.
        this.order.forEach(function (li) {
            dressAsTab(li);
            strip.appendChild(li);
        });

        this.container.classList.remove(RESERVE_CLASS);
        this.more.classList.add(EMPTY_CLASS);

        // 2. If the whole row fits, there is nothing to move. Fall through to
        //    the sync below rather than returning: the chevron's state is a
        //    fact about the menu, and only the menu can be asked for it.
        if (overflows(strip)) {
            // 3. It does not fit, so the chevron is real and needs somewhere to
            //    land: reserve its space BEFORE measuring again, or the row
            //    would be trimmed to a width the chevron then covers.
            this.container.classList.add(RESERVE_CLASS);
            this.more.classList.remove(EMPTY_CLASS);

            // 4. Move from the end — lowest priority first — and stop the
            //    moment the rest fits. Inserting each at the FRONT of the menu
            //    keeps the menu in the same order as the row it came from.
            var movable = this.order.filter(function (li) { return !isActive(li); }).reverse();

            for (var i = 0; i < movable.length && overflows(strip); i++) {
                dressAsMenuItem(movable[i]);
                menu.insertBefore(movable[i], menu.firstChild);
            }

            // Everything that could move, moved, and it still fits nowhere: the
            // pane is narrower than a single tab. The sync below leaves the
            // chevron drawn — what is in the menu is unreachable otherwise.
        }

        // 5. The chevron is drawn if, and only if, something is behind it.
        //
        //    Derived rather than inferred from the branch above, because a
        //    hidden chevron over a full menu is the one failure mode of this
        //    module that a user cannot work around: the tabs are not merely
        //    out of sight, they have no door. Anything that puts a row in the
        //    menu without asking — another script, a restored Turbo snapshot,
        //    a controller that ran before this one — is covered by asking the
        //    menu itself.
        var stranded = menu.children.length > 0;
        this.more.classList.toggle(EMPTY_CLASS, !stranded);
        this.container.classList.toggle(RESERVE_CLASS, stranded);
    };

    /**
     * Put one tab back in the strip, at the place the markup gave it.
     *
     * Anything that shows a tab has to call this first if that tab might be in
     * the menu — see forgetTabInstance() for why a trigger cannot simply be
     * activated where it sits. Re-laying out afterwards is not this function's
     * job: showing the tab makes it active, and layout() will not move an
     * active tab out again.
     */
    Controller.prototype.promote = function (li) {
        if (this.order.indexOf(li) === -1 || li.parentNode === this.strip) return;

        // Authored order: land in front of the first tab that follows this one
        // and is already in the strip, so the row never reshuffles itself.
        var after = null;
        for (var i = this.order.indexOf(li) + 1; i < this.order.length; i++) {
            if (this.order[i].parentNode === this.strip) { after = this.order[i]; break; }
        }

        dressAsTab(li);
        this.strip.insertBefore(li, after);
    };

    /**
     * Coalesce bursts (resize, font swap, a tab activation) into one measure
     * on the next frame. Layout reads scrollWidth, which forces a reflow, so
     * doing it once per frame rather than once per event matters on a drag.
     */
    Controller.prototype.schedule = function () {
        var self = this;
        if (this.frame) return;

        var run = function () {
            self.frame = null;
            self.layout();
        };

        this.frame = window.requestAnimationFrame
            ? window.requestAnimationFrame(run)
            : window.setTimeout(run, 16);
    };

    function attach(container) {
        for (var i = 0; i < controllers.length; i++) {
            if (controllers[i].container === container) return controllers[i];
        }

        var ctrl = new Controller(container);
        if (!ctrl.usable()) return null;

        // Choosing a tab from the menu moves it back into the strip and shows
        // it there. Bootstrap's own delegated handler would fire on the same
        // click and show it too — this runs first (the menu is nearer the
        // target than document is), so by the time it does, the trigger is
        // already home. Plain links in the menu are left alone: they navigate.
        ctrl.menu.addEventListener('click', function (event) {
            var a = event.target.closest ? event.target.closest('[data-bs-toggle="tab"]') : null;
            if (!a || !ctrl.menu.contains(a)) return;

            event.preventDefault();
            ctrl.promote(a.parentNode);

            if (window.bootstrap && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(a).show();
            }
        });

        controllers.push(ctrl);
        ctrl.layout();

        return ctrl;
    }

    function refresh() {
        controllers.forEach(function (c) { c.schedule(); });
    }

    /**
     * Promote whichever tab owns `el` (an anchor or its <li>) back into its
     * strip. Public because a page that activates a tab by key — a deep link,
     * a Back button — has to reach the trigger before Bootstrap does.
     */
    function promote(el) {
        if (!el) return;
        var li = el.tagName === 'LI' ? el : (el.closest ? el.closest('li') : null);
        if (!li) return;

        for (var i = 0; i < controllers.length; i++) {
            if (controllers[i].order.indexOf(li) !== -1) {
                controllers[i].promote(li);
                return;
            }
        }
    }

    function scan(root) {
        var scope = root || document;
        Array.prototype.forEach.call(
            scope.querySelectorAll('[data-awt-tab-overflow]'),
            attach
        );
    }

    window.addEventListener('resize', refresh);

    // A tab that was in the menu is now the one being shown, so it has to come
    // back inline. Deferred to a frame so the dropdown finishes its own close
    // before the element it is closing over is moved out from under it.
    document.addEventListener('shown.bs.tab', refresh, true);

    // Labels are text: a web font that swaps in after first paint changes the
    // width of every tab, and the row measured against the fallback font is
    // then wrong. Re-measure once the real font is in.
    if (document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') {
        document.fonts.ready.then(refresh).catch(function () {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { scan(); });
    } else {
        scan();
    }

    // Turbo swaps the body without a reload, which drops every controller's
    // container on the floor — rescan the new one.
    document.addEventListener('turbo:load', function () {
        controllers.length = 0;
        scan();
    });

    window.AwtTabOverflow = { attach: attach, refresh: refresh, scan: scan, promote: promote };
})();
