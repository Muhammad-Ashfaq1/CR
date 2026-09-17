/**
 * Freeze live glass blur while the page (or any inner pane) is scrolling.
 *
 * backdrop-filter re-filters whatever sits behind every glass surface on
 * every frame. On a dashboard of cards that feels like the UI is loading
 * as you scroll. html.awt-scrolling drops --awt-glass-blur / --msg-blur
 * to none; the translucent fill stays. Bound once (Turbo-safe).
 */
(function () {
    if (window.__awtGlassScrollBound) {
        return;
    }
    window.__awtGlassScrollBound = true;

    var html = document.documentElement;
    var idle = 0;
    var queued = false;

    function mark() {
        queued = false;
        html.classList.add('awt-scrolling');
    }

    function onMove() {
        if (!queued) {
            queued = true;
            requestAnimationFrame(mark);
        }
        clearTimeout(idle);
        idle = setTimeout(function () {
            html.classList.remove('awt-scrolling');
        }, 140);
    }

    var opts = { capture: true, passive: true };
    document.addEventListener('scroll', onMove, opts);
    window.addEventListener('wheel', onMove, opts);
    window.addEventListener('touchmove', onMove, opts);
})();
