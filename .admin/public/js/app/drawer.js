(function () {
    'use strict';
    // Edit-drawer controller: tab switching + child-tab active-state +
    // ac-list chip rendering. Vanilla — no jQuery. Init runs on
    // DOMContentLoaded and again after every AJAX inject (screens.js push)
    // so newly-rendered subtrees pick up bindings.
    //
    // T7 adds .sw-tabnav tab switching. T10 adds .child-tabs active-state
    // management. T19 will add ac-list chip rendering. See
    // docs/superpowers/plans/2026-05-19-edit-drawer-fidelity.md.
    //
    // Architecture note (T10 coexistence decision):
    //   screens.js click delegation (line 494) already catches
    //   [j^='conglet_'][p] clicks, calls e.stopPropagation(), and
    //   pushes the child list as a new .proto-screen via openChildList().
    //   screens.js owns the XHR + navigation; the legacy jQuery handler
    //   emitted in onReadyJs never runs (innerHTML blocks <script>).
    //   This vanilla handler's ONLY job is the .is-active visual-state
    //   toggle + auto-marking the first tab on mount. No XHR here — that
    //   would double-push a screen. No dispatchEvent auto-click — that
    //   would trigger openChildList() immediately on mount (wrong UX).

    function init(root) {
        // --- .sw-tabnav tab switching (T7) ---
        var navs = (root || document).querySelectorAll('.sw-tabnav');
        navs.forEach(function (nav) {
            if (nav.__gcBound) return;
            nav.__gcBound = true;
            nav.addEventListener('click', function (e) {
                var btn = e.target.closest('.tab-btn');
                if (!btn || !nav.contains(btn)) return;
                activateTab(nav, btn);
            });
            nav.addEventListener('keydown', function (e) {
                if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
                var btns = Array.prototype.slice.call(nav.querySelectorAll('.tab-btn'));
                var i = btns.indexOf(document.activeElement);
                if (i < 0) return;
                var j = e.key === 'ArrowLeft' ? (i - 1 + btns.length) % btns.length : (i + 1) % btns.length;
                btns[j].focus();
                e.preventDefault();
            });
        });

        // --- per-field i18n language switcher ---
        // Emitted by the goatcheese form builder around each translated
        // column: .i18n-field > .i18n-langnav (.i18n-lang-btn per locale)
        // + one .i18n-pane per locale (hidden attr on inactive ones, so
        // their inputs still submit). Scoped to the closest .i18n-field —
        // deliberately NOT the .sw-tabnav/.tab-pane machinery, whose
        // activateTab() toggles every pane in the drawer. Document-level
        // delegation so panes injected with later screens need no rebind.
        if (!document.__gcI18nNavBound) {
            document.__gcI18nNavBound = true;
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.i18n-lang-btn');
                if (!btn) return;
                var field = btn.closest('.i18n-field');
                if (!field) return;
                var lang = btn.getAttribute('data-lang');
                field.querySelectorAll('.i18n-lang-btn').forEach(function (b) {
                    var on = (b === btn);
                    b.classList.toggle('is-active', on);
                    b.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                field.querySelectorAll('.i18n-pane').forEach(function (p) {
                    var on = (p.getAttribute('data-lang') === lang);
                    p.classList.toggle('is-active', on);
                    if (on) p.removeAttribute('hidden'); else p.setAttribute('hidden', '');
                });
                // Wysiwyg inside a just-revealed pane: (re)bind sizes it correctly
                if (window.gcEditor && gcEditor.bindWithin) {
                    try { gcEditor.bindWithin(field); } catch (err) {}
                }
            });
        }

        // --- .child-tabs strip (T10) ---
        // Server contract (from goatcheese with_child_tables.php):
        //   button attrs: j="conglet_{Parent}"  p="{ChildClass}"  ip="{parentId}"
        //
        // screens.js handles the actual navigation (openChildList push-screen).
        // It registers its click handler at capture phase on document, so it
        // fires before bubbling-phase listeners and calls stopPropagation() —
        // which would prevent a bubbling listener on .child-tabs from firing.
        // We therefore also use a document-level capture listener. stopPropagation
        // does NOT block other handlers on the same element (only
        // stopImmediatePropagation does), so both this handler and screens.js's
        // handler both fire at the document capture phase. We check for
        // .child-tab membership and update the visual state; screens.js checks
        // for [j^=conglet_] and calls openChildList. No ordering dependency.
        var childNavs = (root || document).querySelectorAll('.child-tabs');
        childNavs.forEach(function (nav) {
            if (nav.__gcChildBound) return;
            nav.__gcChildBound = true;

            // Document-level capture so we run even after screens.js's
            // stopPropagation() halts the event from bubbling to our nav node.
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.child-tab');
                if (!btn || !nav.contains(btn)) return;
                // Visual state only — screens.js openChildList() does the XHR.
                nav.querySelectorAll('.child-tab').forEach(function (b) {
                    var on = (b === btn);
                    b.classList.toggle('is-active', on);
                    b.setAttribute('aria-selected', on ? 'true' : 'false');
                });
            }, true); // capture = true

            // --- Overflow arrows ---
            // Desktop has no scrollbar (hidden in _formv2.scss) and no
            // swipe, so child tabs scrolled out of view were unreachable.
            // Inject sticky prev/next chevrons that scroll the strip. They
            // pin to the visible edges via position:sticky (CSS in
            // _formv2.scss). Hidden entirely when the strip fits; dimmed
            // (kept in layout) at an edge so scrolling never shifts the band.
            // Arrows are not .child-tab, so the active-state handler above
            // and screens.js openChildList both ignore them.
            (function () {
                var prev = document.createElement('button');
                prev.type = 'button';
                prev.className = 'child-tabs-arrow prev is-hidden';
                prev.tabIndex = -1;
                prev.setAttribute('aria-label', 'Scroll tabs left');
                prev.innerHTML = "<i class='ri-arrow-left-s-line'></i>";

                var next = document.createElement('button');
                next.type = 'button';
                next.className = 'child-tabs-arrow next is-hidden';
                next.tabIndex = -1;
                next.setAttribute('aria-label', 'Scroll tabs right');
                next.innerHTML = "<i class='ri-arrow-right-s-line'></i>";

                nav.insertBefore(prev, nav.firstChild);
                nav.appendChild(next);

                function update() {
                    var max = nav.scrollWidth - nav.clientWidth;
                    var overflow = max > 2;
                    prev.classList.toggle('is-hidden', !overflow);
                    next.classList.toggle('is-hidden', !overflow);
                    prev.classList.toggle('is-disabled', nav.scrollLeft <= 1);
                    next.classList.toggle('is-disabled', nav.scrollLeft >= max - 1);
                }
                function step(dir) {
                    nav.scrollBy({ left: dir * Math.round(nav.clientWidth * 0.7), behavior: 'smooth' });
                }
                prev.addEventListener('click', function (e) { e.preventDefault(); step(-1); });
                next.addEventListener('click', function (e) { e.preventDefault(); step(1); });
                nav.addEventListener('scroll', update, { passive: true });
                window.addEventListener('resize', update);
                requestAnimationFrame(update);
                // Width settles after the drawer's slide-in animation.
                setTimeout(update, 350);
            }());

            // No default child selection on mount. The visual .is-active
            // is set only when the user actively clicks a tab — so the
            // child panel stays empty until chosen. Previously we auto-
            // marked the first tab to signal "you have children" but it
            // looked like the panel was loading content that wasn't there.
        });

        // ac-list chip transform: cells emitted by T18 with
        // data-ac-options='{"id":"label",...}' whose textContent is the
        // |id|id| pipe-encoded payload. Replace the textContent with chip
        // spans. Idempotent via __gcChipsBound (runs again after AJAX swaps).
        var acCells = (root || document).querySelectorAll('[data-ac-options]');
        acCells.forEach(function (cell) {
            if (cell.__gcChipsBound) return;
            var raw = (cell.textContent || '').trim();
            var m = raw.match(/^\|([\d|]+)\|$/);
            if (!m) return; // not a pipe-encoded payload — leave alone
            var map;
            try { map = JSON.parse(cell.getAttribute('data-ac-options') || '{}'); }
            catch (e) { return; }
            var ids = m[1].split('|').filter(Boolean);
            var html = ids.map(function (id) {
                var label = map[id];
                if (label === undefined) return ''; // unknown id — omit
                return '<span class="ac-chip">' + window.gcCore.escapeHtml(label) + '</span>'; // #32: shared core
            }).filter(Boolean).join(' ');
            if (html) {
                cell.innerHTML = html;
                cell.__gcChipsBound = true; // mark as transformed only on success
            }
        });

        // (The native-<select> swap experiment is reverted — the user
        // wants to keep the legacy jQuery selectbox widget on desktop
        // and reskin it via CSS to match the boxed input style. CSS in
        // _formv2.scss handles .js-select-label / .select-label-span /
        // .select-element styling. No JS work here.)

        // --- Editable sw-name in identity card ---
        // The h2.sw-name is static text. Bind it to the underlying form
        // input named "Name" (or the first text input whose name ends
        // with "Name") so editing the heading updates the form value
        // before submit. Pure visual edit affordance — no markup change.
        var names = (root || document).querySelectorAll('.proto-screen .sw-drawer .sw-identity .sw-name');
        names.forEach(function (h) {
            if (h.__gcNameBound) return;
            var drawer = h.closest('.sw-drawer');
            if (!drawer) return;
            var input = drawer.querySelector('.sw-body input[name="Name"]')
                || drawer.querySelector('.sw-body input[name="Title"]')
                || drawer.querySelector('.sw-body input[name="Label"]')
                || (function () {
                    var ins = drawer.querySelectorAll('.sw-body input[type="text"]');
                    for (var i = 0; i < ins.length; i++) {
                        if (/name|title|label/i.test(ins[i].name || '')) return ins[i];
                    }
                    return ins[0] || null;
                })();
            if (!input) return;
            h.setAttribute('contenteditable', 'true');
            h.setAttribute('spellcheck', 'false');
            h.setAttribute('role', 'textbox');
            h.setAttribute('aria-label', 'Edit name');
            h.classList.add('is-editable');
            h.addEventListener('input', function () {
                input.value = (h.textContent || '').trim();
                try {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (e) {}
            });
            // Reverse-sync: typing in the underlying input updates the
            // heading too (in case the user edits via the form field).
            input.addEventListener('input', function () {
                if (document.activeElement === h) return;
                h.textContent = input.value;
            });
            h.__gcNameBound = true;
        });

        // --- Legacy inner Tabs.php-style toggles (e.g. Authy form
        // Rights sub-tabs: All / Group / Owner). The emitter wraps these
        // in onReadyJs that depends on jQuery, but screens.js injects
        // forms via innerHTML which strips <script>, so the bindings
        // never run. We replicate the toggle vanilla.
        //
        // Server contract (runtime Html/Tabs.php):
        //   <a class="ui-tabs-anchor" j="conglet_<Parent>" t="#<TargetId>">
        // Plus a wrapper #<parentContentDivId> with direct <div> children
        // whose ids are the targets. We distinguish from child-table tabs
        // (which carry p=/ip= and are handled by screens.js openChildList)
        // by requiring the `t` attribute.
        var tabAnchors = (root || document).querySelectorAll('a.ui-tabs-anchor[j^="conglet_"][t]');
        // Initial state: hide non-selected panels for each tab group. The
        // emitter marks the default <li> with class="selected". We mirror
        // what the legacy onReadyJs did on document.ready.
        var groupsSeen = {};
        tabAnchors.forEach(function (anchor) {
            var groupKey = anchor.getAttribute('j') || '';
            if (!groupKey || groupsSeen[groupKey]) return;
            groupsSeen[groupKey] = true;
            var groupAnchors = document.querySelectorAll('a.ui-tabs-anchor[j="' + groupKey + '"]');
            var defaultAnchor = null;
            groupAnchors.forEach(function (a) {
                if (a.parentNode && a.parentNode.classList && a.parentNode.classList.contains('selected')) {
                    defaultAnchor = a;
                }
            });
            if (!defaultAnchor) { defaultAnchor = groupAnchors[0]; }
            if (!defaultAnchor) return;
            var defaultTarget = defaultAnchor.getAttribute('t');
            if (!defaultTarget) return;
            var defaultPanel = document.querySelector(defaultTarget);
            if (!defaultPanel || !defaultPanel.parentNode) return;
            Array.prototype.forEach.call(defaultPanel.parentNode.children, function (sibling) {
                if (sibling.tagName && sibling.tagName.toLowerCase() === 'div') {
                    sibling.style.display = (sibling === defaultPanel) ? '' : 'none';
                }
            });
        });
        tabAnchors.forEach(function (anchor) {
            if (anchor.__gcLegacyTabBound) return;
            anchor.__gcLegacyTabBound = true;
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                var conglet = anchor.getAttribute('j');
                var target = anchor.getAttribute('t');
                if (!conglet || !target) return;
                var panel = document.querySelector(target);
                if (!panel || !panel.parentNode) return;
                document.querySelectorAll('a.ui-tabs-anchor[j="' + conglet + '"]').forEach(function (a) {
                    if (a.parentNode) {
                        a.parentNode.classList.remove('selected', 'ui-state-active');
                    }
                });
                if (anchor.parentNode) {
                    anchor.parentNode.classList.add('selected', 'ui-state-active');
                }
                Array.prototype.forEach.call(panel.parentNode.children, function (sibling) {
                    if (sibling.tagName && sibling.tagName.toLowerCase() === 'div') {
                        sibling.style.display = 'none';
                    }
                });
                panel.style.display = '';
            });
        });

        // --- Authy USER form: "rights are derived from groups" warning ---
        // The Rights tab (#tab_rights_all) is emitted on BOTH the Authy user
        // form and the AuthyGroup form (both carry is_rights_column), but the
        // two mean different things: on the GROUP form editing rights is the
        // legitimate source of truth, while on the USER form the stored rights
        // are recomputed (OVERWRITTEN) from the user's group membership on every
        // group/primary-group change (GoatCheese postSave recompute). So we warn
        // admins — but ONLY on the user form. We discriminate via the per-entity
        // change-flag hidden the emitter drops in the form save bar: the Authy
        // user form carries input[name="formChangedAuthy"]; the AuthyGroup form
        // carries formChangedAuthyGroup, which the EXACT name match below
        // excludes — so the AuthyGroup form is left untouched.
        var RIGHTS_NOTE_TEXT = "Rights are derived from group membership and are "
            + "overwritten whenever this user's groups or primary group change. "
            + "Edit the user's groups to change access.";
        var rightsPanes = (root || document).querySelectorAll('.tab-pane[data-tab="tab_rights_all"]');
        rightsPanes.forEach(function (pane) {
            if (pane.__gcRightsNoteBound) return;
            pane.__gcRightsNoteBound = true;
            // Scope strictly to the Authy USER form. Bind to the pane's NEAREST
            // form container (.proto-form) — NOT the enclosing .sw-drawer: the
            // user form embeds the AuthyGroupX membership child, whose row-edit
            // opens the far AuthyGroup form (its own .proto-form, also carrying a
            // tab_rights_all pane). Using closest('.sw-drawer') + a descendant
            // query would let that nested AuthyGroup pane resolve to the OUTER
            // Authy drawer and inherit its formChangedAuthy → the warning would
            // leak onto the group's rights matrix. closest('.proto-form') returns
            // each form's own root, and the exact formChangedAuthy name (vs the
            // group form's formChangedAuthyGroup) keeps us on the user form only.
            var formScope = pane.closest('.proto-form') || pane.closest('.sw-drawer') || pane.closest('.proto-screen');
            if (!formScope) return;
            var changed = formScope.querySelector('input[name="formChangedAuthy"]');
            // Guard against an ancestor match leaking in: the matched input must
            // not live inside a more deeply nested form than this pane.
            if (!changed || changed.closest('.proto-form') !== formScope) return;

            // Always-visible note injected at the top of the rights pane. Inline
            // styles keep it self-contained: a project picks this up via the
            // gc build --sync-template JS drift alone — no SCSS recompile needed.
            var note = document.createElement('div');
            note.className = 'gc-rights-derived-note';
            note.setAttribute('role', 'note');
            note.style.cssText = 'margin:0 0 12px;padding:10px 12px;border-radius:8px;'
                + 'background:#fff8e1;border:1px solid #f1c40f;color:#7a5c00;'
                + 'font-size:13px;line-height:1.4;display:flex;align-items:flex-start;gap:6px;';
            var icon = document.createElement('i');
            icon.className = 'ri-information-line';
            icon.style.cssText = 'flex:0 0 auto;margin-top:1px;';
            note.appendChild(icon);
            note.appendChild(document.createTextNode(' ' + RIGHTS_NOTE_TEXT));
            var grid = pane.querySelector('.sw-grid') || pane;
            grid.insertBefore(note, grid.firstChild);

            // Flash + emphasize the note whenever a rights field is edited. The
            // rights inputs are the matrix checkboxes (j^='rcRights') and the
            // per-tab "check all" toggles (j='mass-action-Rights'); a single
            // delegated change/input listener on the pane covers every row and
            // any re-rendered matrix.
            var flashNote = function (e) {
                var t = e.target;
                if (!t || t.tagName !== 'INPUT') return;
                var j = t.getAttribute('j') || '';
                if (j.indexOf('rcRights') !== 0 && j.indexOf('mass-action-Rights') !== 0) return;
                // Persistent emphasis after the first edit.
                note.style.background = '#ffe9a8';
                note.style.borderColor = '#e0a800';
                // Brief pulse to draw the eye (no-op if WAAPI is unavailable).
                if (typeof note.animate === 'function') {
                    note.animate([
                        { boxShadow: '0 0 0 0 rgba(224,168,0,0)' },
                        { boxShadow: '0 0 0 4px rgba(224,168,0,0.45)' },
                        { boxShadow: '0 0 0 0 rgba(224,168,0,0)' }
                    ], { duration: 500, iterations: 1 });
                }
            };
            pane.addEventListener('change', flashNote);
            pane.addEventListener('input', flashNote);
        });

        // MutationObserver scoped to each .child-pannel: re-runs init()
        // when its direct children change (e.g. after screens.js injects a
        // refreshed child subtree) so nested .sw-tabnav / .child-tabs in
        // the new content get bound without watching the whole document.
        var pannels = (root || document).querySelectorAll('.child-pannel');
        pannels.forEach(function (p) {
            if (p.__gcObserved) return;
            p.__gcObserved = true;
            var mo = new MutationObserver(function () { init(p); });
            mo.observe(p, { childList: true, subtree: false });
        });
    }

    function activateTab(nav, btn) {
        var target = btn.getAttribute('data-tab');
        if (!target) return;
        nav.querySelectorAll('.tab-btn').forEach(function (b) {
            var on = (b === btn);
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        var scope = nav.closest('.sw-drawer') || document;
        scope.querySelectorAll('.tab-pane').forEach(function (p) {
            var on = (p.getAttribute('data-tab') === target);
            p.classList.toggle('is-active', on);
            if (on) p.removeAttribute('hidden'); else p.setAttribute('hidden', '');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }
    window.GcDrawer = { init: init };
})();
