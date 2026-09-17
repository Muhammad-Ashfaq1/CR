/**
 * admin-mobile.js
 * ----------------
 * Vanilla-JS behaviours for the admin panel mobile layer.
 * Loaded at the foot of layouts/admin.blade.php via @stack('scripts').
 *
 * Handles two things that CSS alone cannot:
 *
 *   1. Chart tab toggle (< 768 px)
 *      Shows the tab strip and lets Calls/SMS tabs toggle which dataset the
 *      chart renders. On desktop (≥ 768 px) the tab strip stays hidden and
 *      the chart shows both datasets as it always has.
 *
 *   2. Bottom-sheet actions for table rows (< 768 px)
 *      A .awt-admin-kebab button (⋮) opens an .awt-admin-action-sheet for
 *      that row. A backdrop tap closes it.
 *
 * No Alpine, no new library dependency. Pure delegated-event JS.
 * Guard against double-bind on Turbo/Unpoly navigations: the module flag
 * window.__awtAdminMobileBooted prevents any listener from registering twice.
 */
(function () {
    'use strict';

    if (window.__awtAdminMobileBooted) { return; }
    window.__awtAdminMobileBooted = true;

    /* ── 1. Chart tab toggle ────────────────────────────────────────────── */

    var CHART_BREAKPOINT = 768;

    function isMobile() {
        return window.innerWidth < CHART_BREAKPOINT;
    }

    /**
     * Show/hide the tab strip based on the current viewport.
     * Called on load and on resize (debounced).
     */
    function syncChartTabVisibility() {
        var strip = document.getElementById('awt-chart-tab-strip');
        if (!strip) { return; }

        if (isMobile()) {
            strip.classList.remove('d-none');
            // Apply the active tab's visibility state on first show.
            applyActiveChartTab(strip);
        } else {
            strip.classList.add('d-none');
            // Restore both dataset canvases / series visibility on desktop.
            restoreAllChartDatasets();
        }
    }

    /**
     * Read the currently-active tab button and tell the chart library to show
     * only that dataset. Works with the ApexCharts instance the dashboard page
     * exposes on window.awtTrafficChart (admin-command-center.js must set this).
     * Falls back gracefully if no chart instance is found.
     */
    function applyActiveChartTab(strip) {
        var active = strip.querySelector('.awt-admin-chart-tab-btn.is-active');
        if (!active) { return; }
        filterChartToTab(active.dataset.chartTab);
    }

    function filterChartToTab(tab) {
        var chart = window.awtTrafficChart;
        if (!chart || typeof chart.hideSeries !== 'function') { return; }

        if (tab === 'voice') {
            chart.showSeries('Voice');
            chart.hideSeries('Messaging');
        } else {
            chart.showSeries('Messaging');
            chart.hideSeries('Voice');
        }
    }

    function restoreAllChartDatasets() {
        var chart = window.awtTrafficChart;
        if (!chart || typeof chart.showSeries !== 'function') { return; }
        chart.showSeries('Voice');
        chart.showSeries('Messaging');
    }

    /* Delegate tab-strip clicks from the document so we never have to rebind
       after the chart is replaced on a range-button change. */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-chart-tab]');
        if (!btn) { return; }

        var strip = btn.closest('#awt-chart-tab-strip');
        if (!strip) { return; }

        // Deactivate all, activate clicked.
        Array.prototype.forEach.call(
            strip.querySelectorAll('.awt-admin-chart-tab-btn'),
            function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            }
        );
        btn.classList.add('is-active');
        btn.setAttribute('aria-selected', 'true');

        filterChartToTab(btn.dataset.chartTab);
    });

    // Debounced resize listener.
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(syncChartTabVisibility, 120);
    });

    // Run once on load (after the chart has had a tick to initialise).
    setTimeout(syncChartTabVisibility, 50);


    /* ── 2. Bottom-sheet actions ────────────────────────────────────────── */

    // Shared backdrop element — created once, reused for all sheets.
    var backdrop = null;
    var activeSheet = null;

    function getBackdrop() {
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'awt-admin-sheet-backdrop';
            backdrop.setAttribute('aria-hidden', 'true');
            backdrop.addEventListener('click', closeSheet);
            document.body.appendChild(backdrop);
        }
        return backdrop;
    }

    function openSheet(sheet) {
        if (activeSheet && activeSheet !== sheet) { closeSheet(); }
        activeSheet = sheet;
        sheet.classList.add('is-open');
        getBackdrop().classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet() {
        if (activeSheet) {
            activeSheet.classList.remove('is-open');
            activeSheet = null;
        }
        if (backdrop) { backdrop.classList.remove('is-open'); }
        document.body.style.overflow = '';
    }

    // Close on Escape key.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && activeSheet) { closeSheet(); }
    });

    // Delegate .awt-admin-kebab clicks (the ⋮ button in each row).
    document.addEventListener('click', function (e) {
        var kebab = e.target.closest('.awt-admin-kebab');
        if (!kebab) { return; }
        e.preventDefault();

        // Each kebab must carry a data-sheet="#some-id" pointing to its sheet.
        var sheetId = kebab.dataset.sheet;
        var sheet = sheetId ? document.querySelector(sheetId) : null;
        if (!sheet) { return; }

        if (sheet.classList.contains('is-open')) {
            closeSheet();
        } else {
            openSheet(sheet);
        }
    });

    // Close button inside a sheet.
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-awt-sheet-close]')) { closeSheet(); }
    });

})();
