/* GoatCheese vanilla shell — drawer toggle. No jQuery.
 *
 * The builder emits #appDrawer.proto-drawer + #appDim.proto-dim
 * (BuilderLayout::render). The list header emits a .menu-btn; the
 * legacy top-nav still emits a .trigger-menu anchor. Both open the
 * drawer here. SCSS for .proto-drawer / .proto-dim lives in _formv2.
 * The legacy .left-panel is display:none on mobile (kept in DOM for
 * desktop + the isRoot impersonation selectbox JS).
 */
(function () {
    'use strict';

    function drawer() { return document.getElementById('appDrawer'); }
    function dim() { return document.getElementById('appDim'); }

    // The .dr-search-input is never autofocused on open — on touch
    // devices that pops the virtual keyboard over the menu. The user
    // taps it when they want to filter.
    function open() {
        var d = drawer(), m = dim();
        if (d) { d.classList.add('open'); }
        if (m) { m.classList.add('open'); }
        document.body.classList.add('drawer-open');
    }

    function close() {
        var d = drawer(), m = dim();
        if (d) { d.classList.remove('open'); }
        if (m) { m.classList.remove('open'); }
        document.body.classList.remove('drawer-open');
    }

    function isOpen() {
        var d = drawer();
        return !!(d && d.classList.contains('open'));
    }

    // ── Foldable menu sections (set_menu) ──────────────────────────────
    // Each .dr-section-foldable carries data-menu-group; the open/folded
    // choice is persisted per group. localStorage access is guarded so a
    // private-mode failure only loses persistence, not the toggle itself.
    function foldKey(group) { return 'gc.menu.fold.' + group; }

    function readFold(group) {
        try { return window.localStorage.getItem(foldKey(group)); }
        catch (e) { return null; }
    }
    function writeFold(group, value) {
        try { window.localStorage.setItem(foldKey(group), value); }
        catch (e) { /* storage unavailable — toggle still works in-page */ }
    }

    function subOf(section) {
        var n = section.nextElementSibling;
        return (n && n.classList && n.classList.contains('dr-sub')) ? n : null;
    }

    function setFolded(section, folded) {
        section.classList.toggle('is-folded', folded);
        var sub = subOf(section);
        if (sub) { sub.classList.toggle('is-folded', folded); }
    }

    function toggleSection(section) {
        var folded = !section.classList.contains('is-folded');
        setFolded(section, folded);
        var group = section.getAttribute('data-menu-group') || '';
        if (group) { writeFold(group, folded ? 'folded' : 'open'); }
    }

    function applyStoredFolds() {
        var sections = document.querySelectorAll('.dr-section-foldable');
        Array.prototype.forEach.call(sections, function (section) {
            var group = section.getAttribute('data-menu-group') || '';
            if (!group) { return; }
            var stored = readFold(group);
            if (stored === 'open')   { setFolded(section, false); }
            else if (stored === 'folded') { setFolded(section, true); }
            // absent → keep the server-rendered default
        });
    }

    function onClick(e) {
        // Open triggers.
        var trigger = e.target.closest('.menu-btn, .trigger-menu');
        if (trigger) {
            e.preventDefault();
            if (isOpen()) { close(); } else { open(); }
            return;
        }
        // Impersonate toggle is handled by an inline <script> emitted
        // alongside the panel markup (see BuilderLayout::getImpersonatePanel).
        // The inline handler sets window.__gcImpersonateInline as a
        // sentinel so this bundled path stays out of its way and we
        // don't double-toggle once both reach the browser.
        // Toggle a foldable menu section. The header may carry a single
        // dashboard deep-link (.dr-section-dash); a click on that link must
        // navigate, not fold — so let real links through here.
        var section = e.target.closest('.dr-section-foldable');
        if (section) {
            var hdrLink = e.target.closest('a');
            if (hdrLink) {
                var hh = hdrLink.getAttribute('href') || '';
                if (hh && !/^\s*javascript:/i.test(hh)) { close(); }
                return; // allow native navigation; do NOT preventDefault/fold
            }
            e.preventDefault();
            toggleSection(section);
            return;
        }
        // Explicit close affordances.
        if (e.target.closest('.dr-close')) {
            e.preventDefault();
            close();
            return;
        }
        // Tap the dim overlay to dismiss.
        var m = dim();
        if (m && (e.target === m || e.target.closest('#appDim'))) {
            close();
            return;
        }
        // Following a real nav link closes the drawer (it will navigate).
        var link = e.target.closest('#appDrawer .ac-menu a');
        if (link) {
            var href = link.getAttribute('href') || '';
            if (href && !/^\s*javascript:/i.test(href)) {
                close();
            }
        }
    }

    function onKey(e) {
        if (e.key === 'Escape' && isOpen()) { close(); }
    }

    // Client-side .dr-item filter (guideline .dr-search). Not autofocused
    // (focusing an offscreen input scrolls the page) — the user taps it.
    function onFilter(e) {
        var box = e.target;
        if (!box.classList || !box.classList.contains('dr-search-input')) {
            return;
        }
        var d = drawer();
        if (!d) { return; }
        var q = (box.value || '').trim().toLowerCase();
        var items = d.querySelectorAll('.dr-item');
        Array.prototype.forEach.call(items, function (it) {
            var lbl = it.querySelector('.dr-item-label');
            var txt = (lbl ? lbl.textContent : it.textContent || '').toLowerCase();
            it.style.display = (q === '' || txt.indexOf(q) !== -1) ? '' : 'none';
        });
        // Hide a .dr-section whose following items are all hidden.
        Array.prototype.forEach.call(d.querySelectorAll('.dr-section'), function (sec) {
            var any = false, n = sec.nextElementSibling;
            while (n && !n.classList.contains('dr-section')) {
                if (n.classList.contains('dr-item') && n.style.display !== 'none') { any = true; break; }
                if (n.querySelector && n.querySelector('.dr-item:not([style*="display: none"])')) { any = true; break; }
                n = n.nextElementSibling;
            }
            sec.style.display = (q === '' || any) ? '' : 'none';
        });
    }

    function init() {
        // Capture phase: legacy jQuery still binds some nav controls and
        // calls stopPropagation, which would starve a bubble-phase
        // document delegate. Capture runs root→target, before that.
        document.addEventListener('click', onClick, true);
        document.addEventListener('keydown', onKey, true);
        document.addEventListener('input', onFilter, false);
        applyStoredFolds();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();
