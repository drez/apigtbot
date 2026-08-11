/* GoatCheese Region 4 — advanced-filter surface client (vanilla).
 *
 * Owns the schema-derived filter sheet/popover the emitter emits inside
 * #formMs<Table> (.va-filter-surface). No jQuery, no alert/confirm/prompt.
 *
 * Hard invariant (spec §1/§4): the serialized `ms` is byte-equivalent in
 * shape to the legacy per-field search. This module NEVER serializes or
 * fetches itself — it only mutates the EXISTING per-field named inputs
 * the form already emits (the same nodes serializeSearch reads), then
 * defers to list.js's single serialize/fetch path via
 * window.gcList.applyFilter(form). Specifically:
 *   - SEARCH box is name-less (never serializes). On Apply its value is
 *     fanned into the active SEARCH-IN chips' target per-field text
 *     inputs; inactive targets are blanked. Identical to typing that term
 *     into each legacy per-field box.
 *   - segmented / multi chips toggle the value of the existing
 *     selectboxCustomArray-backed hidden input ([name="X"] single,
 *     [name="X[]"] comma-joined for multi) — exactly the value the legacy
 *     SelectBox widget produces, so the GET ms is byte-identical.
 *   - the relocated FK / large-ENUM select IS the existing widget; the
 *     user drives it directly (it mutates its own hidden input).
 *
 * Open/close mirror the existing .va-mob-sortsheet / .proto-sheet infra
 * (list.js / screens.js): toggle `hidden` + `.open`, transition the
 * panel, focus only after transitionend (no offscreen-autofocus jump).
 */
(function () {
    'use strict';

    function qsa(el, sel) {
        return el ? Array.prototype.slice.call(el.querySelectorAll(sel)) : [];
    }

    var DESKTOP_BP = 720;          // matches _desktopv2.scss media query
    var PANEL_W = 680;             // matches _desktopv2 .va-filter-panel
    var open = null;               // { form, surface, panel, snapshot, restore }

    function isDesktop() {
        return window.innerWidth >= DESKTOP_BP;
    }

    function surfaceFor(form) {
        return form ? form.querySelector('.va-filter-surface') : null;
    }

    // The per-field control a block targets. seg/single → [name="post"];
    // multi → [name="post[]"]. Returns the live form node (hidden input
    // when SelectBox-backed, or a real select/input).
    function targetInput(form, field) {
        return form.querySelector(
            'select[name="' + field + '"], select[name="' + field + '[]"],'
            + ' input[name="' + field + '"], input[name="' + field + '[]"],'
            + ' textarea[name="' + field + '"]'
        );
    }

    // ----- value get/set that matches the legacy SelectBox encoding -----
    // Single select / segmented: scalar value. Multi: comma-joined set
    // (the exact encoding selectbox.js stores in the hidden input — it
    // does input.val(arrayValues) which joins by comma, and reads via
    // val().split(',') — see selectbox.js updateValue/updateSelected).
    function getMulti(node) {
        if (!node) { return []; }
        if (node.tagName === 'SELECT' && node.multiple) {
            return Array.prototype.map.call(node.selectedOptions, function (o) {
                return o.value;
            });
        }
        var v = String(node.value || '');
        return v === '' ? [] : v.split(',');
    }
    function setMulti(node, vals) {
        if (!node) { return; }
        if (node.tagName === 'SELECT' && node.multiple) {
            Array.prototype.forEach.call(node.options, function (o) {
                o.selected = vals.indexOf(o.value) !== -1;
            });
        } else {
            node.value = vals.join(',');
        }
        syncWidget(node);
    }
    function setSingle(node, val) {
        if (!node) { return; }
        if (node.tagName === 'SELECT') {
            node.value = val;
        } else {
            node.value = val;
        }
        syncWidget(node);
    }

    // Best-effort visual re-sync of the legacy SelectBox label widget
    // (the <label class="js-select-label"> sibling that wraps this hidden
    // input) so the relocated control visually reflects the chip choice.
    // Byte-equivalence does NOT depend on this (it depends on the hidden
    // input value, which we set above) — this is presentation only and
    // uses plain DOM (no jQuery).
    function syncWidget(node) {
        try {
            node.dispatchEvent(new Event('input', { bubbles: true }));
            node.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) { /* old engines: ignore */ }
        var name = node.getAttribute('name');
        if (!name) { return; }
        var label = node.parentNode &&
            node.parentNode.querySelector('label.js-select-label[data-name="'
                + name + '"]');
        if (!label) { return; }
        var set = getMulti(node);
        var labels = [];
        qsa(label, 'ul.select-element li').forEach(function (li) {
            var dv = li.getAttribute('data-value');
            var on = dv !== 'default' && set.indexOf(dv) !== -1;
            li.classList.toggle('selected', on);
            if (on) { labels.push(li.getAttribute('data-label') || dv); }
        });
        var span = label.querySelector('.select-label-span');
        if (span) {
            if (labels.length) {
                span.textContent = labels.join(', ');
                span.classList.remove('gray');
            } else {
                var def = label.querySelector('ul.select-element li.default');
                span.textContent = def ? (def.getAttribute('data-label') || '') : '';
                span.classList.add('gray');
            }
        }
    }

    // ----- snapshot / restore (Cancel is non-committal) -----
    // Segmented/multi chips write straight to the live form nodes, so
    // Cancel must restore the pre-open per-field state exactly.
    function takeSnapshot(form, surface) {
        var snap = [];
        qsa(surface, '[data-msfield]').forEach(function (blk) {
            var f = blk.getAttribute('data-msfield');
            var node = targetInput(form, f);
            if (node) {
                snap.push({
                    node: node,
                    multi: node.tagName === 'SELECT' && node.multiple,
                    value: node.value,
                    sel: node.tagName === 'SELECT'
                        ? Array.prototype.map.call(node.options, function (o) {
                            return o.selected;
                        })
                        : null
                });
            }
        });
        // FK / large-ENUM select blocks have no data-msfield (the user
        // drives the relocated widget directly) — snapshot their hidden
        // inputs too so Cancel is exact.
        qsa(surface, '.va-fselect input[name], .va-fselect select[name]')
            .forEach(function (node) {
                snap.push({
                    node: node,
                    multi: node.tagName === 'SELECT' && node.multiple,
                    value: node.value,
                    sel: node.tagName === 'SELECT'
                        ? Array.prototype.map.call(node.options, function (o) {
                            return o.selected;
                        })
                        : null
                });
            });
        return snap;
    }
    function restoreSnapshot(snap) {
        snap.forEach(function (s) {
            s.node.value = s.value;
            if (s.sel && s.node.tagName === 'SELECT') {
                Array.prototype.forEach.call(s.node.options, function (o, i) {
                    o.selected = !!s.sel[i];
                });
            }
            syncWidget(s.node);
        });
    }

    // ----- chip / segment UI sync on open (reflect live form state) -----
    function syncControlsFromForm(form, surface) {
        // SEARCH input: left empty (it is a fan-out source, not a mirror —
        // spec Q1). SEARCH-IN chips keep their emitted default-active set.
        // Segmented: mark the segment matching the live single value.
        qsa(surface, '.va-fseg[data-msfield]').forEach(function (seg) {
            var node = targetInput(form, seg.getAttribute('data-msfield'));
            var cur = node ? String(node.value || '') : '';
            qsa(seg, '.va-fseg-opt').forEach(function (b) {
                b.classList.toggle('active',
                    (b.getAttribute('data-msval') || '') === cur);
            });
            // nothing matched → activate the neutral (first) segment
            if (!seg.querySelector('.va-fseg-opt.active')) {
                var n = seg.querySelector('.va-fseg-opt');
                if (n) { n.classList.add('active'); }
            }
        });
        // Multi chips: mark every chip whose value is in the live set.
        qsa(surface, '.va-fchips-multi[data-msfield]').forEach(function (wrap) {
            var node = targetInput(form, wrap.getAttribute('data-msfield'));
            var set = getMulti(node);
            qsa(wrap, '.va-fchip').forEach(function (c) {
                c.classList.toggle('active',
                    set.indexOf(c.getAttribute('data-msval') || '') !== -1);
            });
        });
    }

    // ----- open / close -----
    function positionPanel(panel, btn) {
        // Mobile: CSS owns it (bottom sheet). Desktop: anchored popover
        // under the filter button, clamped; centered-modal fallback when
        // it can't fit (spec §6 Q5).
        if (!isDesktop()) {
            panel.style.top = '';
            panel.style.left = '';
            panel.classList.remove('va-filter-centered');
            return;
        }
        var vw = window.innerWidth;
        if (PANEL_W + 32 > vw) {
            panel.classList.add('va-filter-centered');
            panel.style.top = '';
            panel.style.left = '';
            return;
        }
        panel.classList.remove('va-filter-centered');
        var r = btn.getBoundingClientRect();
        var left = Math.min(Math.max(r.left, 16), vw - PANEL_W - 16);
        var top = r.bottom + 8;
        // The panel is position:fixed, but a drawer ancestor that
        // establishes a containing block (e.g. .proto-screen carries
        // will-change:transform for child-list filters) makes fixed
        // coords resolve relative to IT, not the viewport. Re-base the
        // viewport-clamped left/top into that containing block so the
        // popover lands under the button instead of overflowing.
        var cb = fixedContainingBlock(panel);
        if (cb) {
            var cr = cb.getBoundingClientRect();
            left -= cr.left;
            top -= cr.top;
        }
        panel.style.top = top + 'px';
        panel.style.left = left + 'px';
    }

    // Nearest ancestor that establishes a containing block for
    // position:fixed descendants (transform / perspective / filter /
    // will-change). Returns null when the viewport is the containing
    // block (the common top-level-list case).
    function fixedContainingBlock(el) {
        var p = el.parentElement;
        while (p && p !== document.documentElement) {
            var cs = getComputedStyle(p);
            if ((cs.transform && cs.transform !== 'none')
                || (cs.perspective && cs.perspective !== 'none')
                || (cs.filter && cs.filter !== 'none')
                || (cs.willChange
                    && /transform|perspective|filter/.test(cs.willChange))) {
                return p;
            }
            p = p.parentElement;
        }
        return null;
    }

    function focusable(panel) {
        return qsa(panel,
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        ).filter(function (el) {
            return !el.disabled && el.offsetParent !== null;
        });
    }

    function doOpen(form) {
        if (!form || open) { return; }
        var surface = surfaceFor(form);
        if (!surface) { return; }
        var panel = surface.querySelector('.va-filter-panel');
        var btn = form.querySelector('.va-mob-filter-btn');

        var snapshot = takeSnapshot(form, surface);
        syncControlsFromForm(form, surface);

        surface.hidden = false;
        positionPanel(panel, btn);
        // force reflow so the transition runs
        // eslint-disable-next-line no-unused-expressions
        panel.offsetWidth;
        surface.classList.add('open');

        // Rebind the vanilla SelectBox widget on the freshly-visible filter
        // surface. list.js's enhance() clones #formMs{T} (to drop legacy
        // pager/sort handlers), which also drops the widget's bindings.
        // bindWithin() guards per-label on _gcBound, so calling it on every
        // open is safe. Mirrors screens.js.
        try {
            if (window.gcSelectBox) {
                gcSelectBox.bindWithin(surface);
            }
        } catch (e) { /* selectbox.js absent; nothing to bind */ }
        try {
            if (window.gcColorField) { gcColorField.bindWithin(surface); }
        } catch (e) { /* colorfield.js absent; nothing to bind */ }

        // The same enhance() clone also drops the gcAutocomplete listeners on
        // the FK filter inputs (Company/Source/Country/Assigned-to); re-attach
        // them so the filter autocomplete fires, mirroring the SelectBox rebind.
        try {
            if (window.gcAutocomplete && gcAutocomplete.rebindWithin) {
                gcAutocomplete.rebindWithin(surface);
            }
        } catch (e) { /* autocomplete.js absent; nothing to bind */ }

        open = {
            form: form, surface: surface, panel: panel,
            btn: btn, snapshot: snapshot
        };

        // Focus the panel only AFTER it has transitioned in — focusing an
        // offscreen/animating sheet scrolls the page (the documented
        // gotcha). Then trap Tab inside the panel.
        var onEnd = function () {
            panel.removeEventListener('transitionend', onEnd);
            try { panel.focus(); } catch (e) { /* non-focusable */ }
        };
        panel.addEventListener('transitionend', onEnd);
        setTimeout(onEnd, 400); // fallback if transitionend never fires
    }

    function doClose(commit) {
        if (!open) { return; }
        var o = open;
        open = null;
        if (!commit) { restoreSnapshot(o.snapshot); }
        o.surface.classList.remove('open');
        var done = function () {
            o.panel.removeEventListener('transitionend', done);
            o.surface.hidden = true;
        };
        o.panel.addEventListener('transitionend', done);
        setTimeout(done, 400);
        if (o.btn && typeof o.btn.focus === 'function') {
            try { o.btn.focus(); } catch (e) { /* ignore */ }
        }
    }

    // ----- Clear all: reset the surface only (no fetch, no close) -----
    function clearAll() {
        if (!open) { return; }
        var form = open.form, surface = open.surface;
        // SEARCH box
        var sb = surface.querySelector('.va-fsearch-input');
        if (sb) { sb.value = ''; }
        // SEARCH-IN chips → emitted default-active set (first 4). The
        // emitter marked them with .active; "reset" = restore that. We
        // approximate by re-activating the first 4 chips.
        var inChips = qsa(surface, '[data-block="searchin"] .va-fchip');
        inChips.forEach(function (c, i) {
            c.classList.toggle('active', i < 4);
        });
        // Blank every text-like SEARCH-IN target.
        inChips.forEach(function (c) {
            var n = targetInput(form, c.getAttribute('data-msfield'));
            if (n) { n.value = ''; }
        });
        // Segmented → neutral segment.
        qsa(surface, '.va-fseg[data-msfield]').forEach(function (seg) {
            var n = targetInput(form, seg.getAttribute('data-msfield'));
            if (n) { setSingle(n, ''); }
            qsa(seg, '.va-fseg-opt').forEach(function (b, i) {
                b.classList.toggle('active', i === 0);
            });
        });
        // Multi chips → none selected.
        qsa(surface, '.va-fchips-multi[data-msfield]').forEach(function (wrap) {
            var n = targetInput(form, wrap.getAttribute('data-msfield'));
            if (n) { setMulti(n, []); }
            qsa(wrap, '.va-fchip').forEach(function (c) {
                c.classList.remove('active');
            });
        });
        // FK / large-ENUM selects → first option / empty.
        qsa(surface, '.va-fselect input[name], .va-fselect select[name]')
            .forEach(function (n) {
                if (n.tagName === 'SELECT') {
                    n.selectedIndex = 0;
                } else {
                    n.value = '';
                }
                syncWidget(n);
            });
    }

    // ----- Apply: fan SEARCH term, then reuse list.js serialize/fetch ---
    function apply() {
        if (!open) { return; }
        var form = open.form, surface = open.surface;
        var sb = surface.querySelector('.va-fsearch-input');
        var term = sb ? String(sb.value || '').trim() : '';
        qsa(surface, '[data-block="searchin"] .va-fchip').forEach(function (c) {
            var node = targetInput(form, c.getAttribute('data-msfield'));
            if (!node) { return; }
            node.value = c.classList.contains('active') ? term : '';
        });
        // If there is a SEARCH block but NO SEARCH-IN block (exactly one
        // text-like column → SEARCH only), fan into that single column.
        if (term !== '' && !surface.querySelector('[data-block="searchin"]')) {
            var only = surface.querySelector(
                '[data-block="search"]'
            ) && open.form.querySelector(
                '.va-mob-search-inline input:not([type="hidden"])'
            );
            if (only) { only.value = term; }
        }
        // segmented / multi / select already wrote to the live nodes.
        doClose(true);
        if (window.gcList && typeof window.gcList.applyFilter === 'function') {
            window.gcList.applyFilter(form);
        }
    }

    // ----- delegated events -----
    function onClick(e) {
        var t = e.target;

        // Close affordances
        if (open) {
            if (t.closest('.sheet-close') || t.closest('.va-fcancel')) {
                e.preventDefault();
                doClose(false);
                return;
            }
            if (t.closest('.va-filter-dim')) {
                e.preventDefault();
                doClose(false);
                return;
            }
            if (t.closest('.va-fclear')) {
                e.preventDefault();
                clearAll();
                return;
            }
            if (t.closest('.va-fapply')) {
                e.preventDefault();
                apply();
                return;
            }
            // Desktop outside-click (capture handler also covers this).
            if (isDesktop()
                && !t.closest('.va-filter-panel')
                && !t.closest('.va-mob-filter-btn')) {
                doClose(false);
                return;
            }
        }

        // SEARCH-IN chip toggle (multi-select of which text fields the
        // term fans into — no fetch here, Apply commits).
        var inChip = t.closest('[data-block="searchin"] .va-fchip');
        if (inChip && open && open.surface.contains(inChip)) {
            e.preventDefault();
            inChip.classList.toggle('active');
            return;
        }

        // Segmented option: set the single live value, move the thumb.
        var seg = t.closest('.va-fseg-opt');
        if (seg && open && open.surface.contains(seg)) {
            e.preventDefault();
            var segWrap = seg.closest('.va-fseg');
            var node = targetInput(open.form,
                segWrap.getAttribute('data-msfield'));
            setSingle(node, seg.getAttribute('data-msval') || '');
            qsa(segWrap, '.va-fseg-opt').forEach(function (b) {
                b.classList.toggle('active', b === seg);
            });
            return;
        }

        // Multi chip: toggle the value in the comma-joined live set.
        var mChip = t.closest('.va-fchips-multi .va-fchip');
        if (mChip && open && open.surface.contains(mChip)) {
            e.preventDefault();
            var wrap = mChip.closest('.va-fchips-multi');
            var mNode = targetInput(open.form,
                wrap.getAttribute('data-msfield'));
            var val = mChip.getAttribute('data-msval') || '';
            var set = getMulti(mNode);
            var i = set.indexOf(val);
            if (i === -1) { set.push(val); } else { set.splice(i, 1); }
            setMulti(mNode, set);
            mChip.classList.toggle('active', i === -1);
            return;
        }
    }

    // Desktop outside-click in the CAPTURE phase so it beats any legacy
    // stopPropagation (mirrors screens.js / list.js).
    function onClickCapture(e) {
        if (!open || !isDesktop()) { return; }
        if (e.target.closest('.va-filter-panel')) { return; }
        if (e.target.closest('.va-mob-filter-btn')) { return; }
        doClose(false);
    }

    function onKey(e) {
        if (!open) { return; }
        if (e.key === 'Escape' || e.keyCode === 27) {
            e.preventDefault();
            e.stopPropagation();   // beat list.js onPageKey Escape
            doClose(false);
            return;
        }
        // Focus trap: keep Tab inside the panel while open.
        if (e.key === 'Tab' || e.keyCode === 9) {
            var f = focusable(open.panel);
            if (!f.length) { return; }
            var first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }

    function onResize() {
        if (open) { positionPanel(open.panel, open.btn); }
    }

    function init() {
        // Capture beats legacy handlers (same rationale as screens.js).
        document.addEventListener('click', onClickCapture, true);
        document.addEventListener('click', onClick, false);
        document.addEventListener('keydown', onKey, true);
        window.addEventListener('resize', onResize, false);
    }

    window.gcFilter = { open: doOpen, close: function () { doClose(false); } };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();
