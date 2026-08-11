/* GoatCheese vanilla autocomplete — replaces jQuery-UI .autocomplete().
 *
 * Drives the FK text inputs emitted by set_autocomplete (input[j='autocomplete']
 * with a sibling hidden id field) and the hardcoded Iarc impersonation search.
 * The legacy global wrap_autoc() (index.js) is now a thin shim that resolves the
 * input/hidden selectors + an endpoint + a contract-agnostic buildData(term) and
 * hands them here, so the emitted onReadyJs and the server JSON contract are
 * unchanged ({count, data:[{id, show}]}).
 *
 * jQuery is still loaded app-wide, but this module is plain DOM/fetch — no
 * jQuery, no jQuery-UI. Desktop: an anchored dropdown with keyboard nav.
 * Mobile (<720px, matching screens.js): the same menu skinned as a bottom
 * sheet (.gc-ac-menu.is-sheet, styled in _formv2.scss).
 */
window.gcAutocomplete = (function () {
    'use strict';

    var DEBOUNCE_MS = 250;
    var BLUR_MS = 180; // let a click on a menu row land before blur-resolve

    function isDesktop() {
        return !!(window.matchMedia && window.matchMedia('(min-width: 720px)').matches);
    }

    // jQuery $.param-compatible enough for the autoc contract: arrays emit as
    // key[]=v1&key[]=v2 so PHP parses them back to an array (the Service also
    // accepts a comma string, but this matches the legacy jQuery-UI request).
    function toQuery(data) {
        var parts = [];
        Object.keys(data).forEach(function (k) {
            var v = data[k];
            if (v == null) { return; }
            if (Array.isArray(v)) {
                v.forEach(function (item) {
                    parts.push(encodeURIComponent(k) + '[]=' + encodeURIComponent(item));
                });
            } else {
                parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
            }
        });
        return parts.join('&');
    }

    function fetchResults(url, data) {
        var sep = url.indexOf('?') === -1 ? '?' : '&';
        return fetch(url + sep + toQuery(data), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.ok ? r.json() : null; })
            .catch(function () { return null; });
    }

    function closeMenu(state) {
        if (state.menu && state.menu.parentNode) {
            state.menu.parentNode.removeChild(state.menu);
        }
        if (state.dim && state.dim.parentNode) {
            state.dim.parentNode.removeChild(state.dim);
        }
        state.menu = null;
        state.dim = null;
        state.items = [];
        state.active = -1;
    }

    function positionMenu(input, menu) {
        var r = input.getBoundingClientRect();
        menu.style.position = 'absolute';
        menu.style.left = (r.left + window.pageXOffset) + 'px';
        menu.style.top = (r.bottom + window.pageYOffset) + 'px';
        menu.style.minWidth = r.width + 'px';
    }

    function renderMenu(state, results) {
        closeMenu(state);
        var desktop = isDesktop();
        var menu = document.createElement('ul');
        menu.className = 'gc-ac-menu' + (desktop ? '' : ' is-sheet');

        if (!results.length) {
            var empty = document.createElement('li');
            empty.className = 'gc-ac-empty';
            empty.textContent = 'No results';
            menu.appendChild(empty);
        } else {
            results.forEach(function (item, i) {
                var li = document.createElement('li');
                li.className = 'gc-ac-item';
                li.setAttribute('data-idx', String(i));
                li.textContent = item.label;
                // mousedown (not click) so it fires before the input's blur.
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectItem(state, item);
                });
                menu.appendChild(li);
            });
        }

        if (!desktop) {
            var dim = document.createElement('div');
            dim.className = 'gc-ac-dim';
            dim.addEventListener('mousedown', function () { closeMenu(state); });
            document.body.appendChild(dim);
            state.dim = dim;
        }

        document.body.appendChild(menu);
        if (desktop) { positionMenu(state.input, menu); }
        state.menu = menu;
        state.items = results;
        state.active = -1;
    }

    function highlight(state, idx) {
        if (!state.menu) { return; }
        var lis = state.menu.querySelectorAll('.gc-ac-item');
        if (!lis.length) { return; }
        if (idx < 0) { idx = lis.length - 1; }
        if (idx >= lis.length) { idx = 0; }
        for (var i = 0; i < lis.length; i++) {
            lis[i].classList.toggle('active', i === idx);
        }
        state.active = idx;
        lis[idx].scrollIntoView({ block: 'nearest' });
    }

    function setValue(state, label, id) {
        state.input.value = label;
        if (state.hidden) {
            state.hidden.value = id;
            state.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function clearValue(state) {
        if (state.hidden && state.hidden.value !== '') {
            state.hidden.value = '';
            state.hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function selectItem(state, item) {
        if (item == null || item.value === 0 || item.value === '0' || item.value === '') {
            return;
        }
        setValue(state, item.label, item.value);
        closeMenu(state);
    }

    function runSearch(state, term, browse) {
        var data0 = state.buildData(term);
        // Browse mode (empty-term focus/click): show the first few options
        // alphabetically so a small FK set behaves like a selectbox.
        if (browse) { data0.limit = 10; }
        fetchResults(state.url, data0).then(function (data) {
            if (!data) { return; }
            // count==1 → autofill immediately (legacy behaviour) — but never
            // from browse mode: focusing a field must not commit a value.
            if (!browse && data.count === 1 && data.data && data.data[0]) {
                setValue(state, data.data[0].show, data.data[0].id);
            }
            var results = (data.count && data.data) ? data.data.map(function (it) {
                return { label: it.show, value: it.id };
            }) : [];
            // Only show the menu if the input still has focus.
            if (document.activeElement === state.input) {
                renderMenu(state, results);
            }
        });
    }

    // Blur-resolve: hidden empty but text typed → re-query and accept a unique
    // match, else clear the orphan text (the typed value is stashed on saisVal
    // exactly as the legacy wrap_autoc did).
    function resolveOnBlur(state) {
        if (!state.hidden) { return; }
        var typed = state.input.value;
        if (state.hidden.value !== '' || typed === '') {
            if (typed === '') { clearValue(state); }
            return;
        }
        fetchResults(state.url, state.buildData(typed)).then(function (data) {
            if (data && data.count === 1 && data.data && data.data[0]) {
                setValue(state, data.data[0].show, data.data[0].id);
            } else {
                state.input.setAttribute('saisVal', typed);
                state.input.value = '';
                clearValue(state);
            }
        });
    }

    function onKeyDown(state, e) {
        var key = e.which || e.keyCode;
        if (key === 40) { // down
            e.preventDefault();
            if (state.menu) { highlight(state, state.active + 1); }
            return;
        }
        if (key === 38) { // up
            e.preventDefault();
            if (state.menu) { highlight(state, state.active - 1); }
            return;
        }
        if (key === 27) { // esc
            closeMenu(state);
            return;
        }
        if (key === 13 || key === 9) { // enter / tab
            if (state.menu && state.active >= 0 && state.items[state.active]) {
                if (key === 13) { e.preventDefault(); }
                selectItem(state, state.items[state.active]);
            } else if (state.input.value === '' || (state.hidden && state.hidden.value === '')) {
                clearValue(state);
            }
            closeMenu(state);
        }
    }

    // Registry of every bound config, keyed by input selector. list.js's
    // enhance() clones #formMs<Table> on each fragment swap (and the filter
    // panel surface shows a clone); the clone keeps the data-gc-ac marker but
    // loses its listeners, so rebindWithin() can re-attach them from here.
    var registry = {};

    // Resolve the hidden id field. Prefer the input's rid attribute (the
    // canonical target the emitter sets) via getElementById, which tolerates
    // the "[]" of multi-select search fields (e.g. IdCompany[]) that a CSS
    // "#IdCompany[]" selector cannot. Fall back to the computed hiddenSel.
    function resolveHidden(input, hiddenSel) {
        var rid = input.getAttribute('rid');
        if (rid) {
            var byId = document.getElementById(rid);
            if (byId && byId !== input) { return byId; }
        }
        return hiddenSel ? document.querySelector(hiddenSel) : null;
    }

    function bind(opts) {
        var input = document.querySelector(opts.inputSel);
        if (!input) { return; }
        registry[opts.inputSel] = opts;
        if (input.getAttribute('data-gc-ac') === '1') { return; }
        input.setAttribute('data-gc-ac', '1');
        input.setAttribute('autocomplete', 'off');

        var state = {
            input: input,
            hidden: resolveHidden(input, opts.hiddenSel),
            url: opts.url,
            minLength: opts.minLength || 2,
            buildData: opts.buildData,
            menu: null, dim: null, items: [], active: -1, timer: null
        };

        input.addEventListener('input', function () {
            clearTimeout(state.timer);
            var term = input.value;
            if (term.length < state.minLength) {
                closeMenu(state);
                if (term === '') { clearValue(state); }
                return;
            }
            state.timer = setTimeout(function () { runSearch(state, term); }, DEBOUNCE_MS);
        });
        input.addEventListener('keydown', function (e) { onKeyDown(state, e); });
        // Empty field + focus/click → browse: list the first options
        // alphabetically (selectbox feel for small FK sets). Skipped for
        // high-minLength searches (e.g. the Iarc user lookup).
        function browseIfEmpty() {
            if (state.minLength > 2) { return; }
            if (input.value !== '' || state.menu) { return; }
            // focus + click arrive together on a fresh click — one fetch.
            if (state.browsing) { return; }
            state.browsing = true;
            setTimeout(function () { state.browsing = false; }, 400);
            runSearch(state, '', true);
        }
        input.addEventListener('focus', function () { input.select(); browseIfEmpty(); });
        input.addEventListener('click', browseIfEmpty);
        input.addEventListener('blur', function () {
            setTimeout(function () {
                closeMenu(state);
                resolveOnBlur(state);
            }, BLUR_MS);
        });
    }

    // Re-attach listeners to any registered autocomplete input inside root
    // whose live element lost them to a clone (data-gc-ac present but dead).
    // Called by filter.js when the advanced-filter panel opens, mirroring
    // gcSelectBox.bindWithin().
    function rebindWithin(root) {
        if (!root) { return; }
        Object.keys(registry).forEach(function (sel) {
            var el = document.querySelector(sel);
            if (el && root.contains(el)) {
                el.removeAttribute('data-gc-ac');
                bind(registry[sel]);
            }
        });
    }

    // depends_on cascade for an autocomplete input (#23 S5 / S6): when any
    // source field changes, clear the dependent selection (the hidden's change
    // event cascades to further dependents); with require=true, disable the
    // input + show a hint placeholder until every source has a value. Ports the
    // inline cascade IIFE the emitter used to ship in the form onReadyJs.
    // casc = { srcs:[hostColIds], hidden:hiddenId, require:bool, requireMsg:str }.
    function wireCascade(input, formId, casc) {
        if (!casc || input.__gcCascBound) { return; }
        input.__gcCascBound = 1;
        var scopeSel = formId ? ('#' + formId + ' ') : '';
        var srcs = (casc.srcs || []).map(function (c) { return scopeSel + '#' + c; });
        var scope = (formId && document.getElementById(formId)) || document;
        var hid = casc.hidden ? scope.querySelector("[id='" + casc.hidden + "']") : null;
        if (!hid) { return; }
        var ph = input.getAttribute('placeholder') || '';
        function hasAll() {
            for (var i = 0; i < srcs.length; i++) {
                var s = document.querySelector(srcs[i]);
                if (!s || s.value === '') { return false; }
            }
            return true;
        }
        function sync(clear) {
            if (clear) {
                if (hid.value !== '') { hid.value = ''; hid.dispatchEvent(new Event('change', { bubbles: true })); }
                input.value = '';
            }
            if (casc.require) {
                var ok = hasAll();
                input.disabled = !ok;
                input.placeholder = ok ? ph : (casc.requireMsg || '');
            }
        }
        sync(false);
        srcs.forEach(function (sel) {
            var s = document.querySelector(sel);
            if (s) { s.addEventListener('change', function () { sync(true); }); }
        });
    }

    // Declarative binder (#23 S5 / S6): bind every [data-gc-autoc] input in
    // scope, the replacement for the per-field inline wrap_autoc(...) onReadyJs
    // the emitter used to ship (re-executed by the screens.js push() arm).
    // Reuses the global wrap_autoc shim, passing the live form id as
    // formParentFull so the scoped #form/#formMs selectors resolve correctly.
    // Idempotent (__gcAutocBound). config = { name, table, childTable, spec }.
    function bindWithin(scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        var inputs = Array.prototype.slice.call(scope.querySelectorAll('[data-gc-autoc]'));
        if (scope.getAttribute && scope.getAttribute('data-gc-autoc') != null) { inputs.push(scope); }
        inputs.forEach(function (input) {
            if (input.__gcAutocBound) { return; }
            input.__gcAutocBound = 1;
            var cfg;
            try { cfg = JSON.parse(input.getAttribute('data-gc-autoc')); } catch (e) { return; }
            if (!cfg) { return; }
            var form = input.closest ? input.closest('form') : null;
            var formId = form ? form.id : '';
            if (typeof window.wrap_autoc === 'function') {
                window.wrap_autoc(cfg.name, cfg.table, cfg.childTable, '', '', '', cfg.version || 'std', cfg.spec || {}, 0, '', formId);
            }
            var cascRaw = input.getAttribute('data-gc-autoc-cascade');
            if (cascRaw) { try { wireCascade(input, formId, JSON.parse(cascRaw)); } catch (e) { /* malformed cascade */ } }
        });
    }

    // Self-init on the top-level page (lists with search autocomplete); pushed
    // fragments are covered by screens.js push() → gcAutocomplete.bindWithin.
    (function () {
        function go() { try { bindWithin(document); } catch (e) {} }
        if (document.readyState != 'loading') { go(); }
        else { document.addEventListener('DOMContentLoaded', go); }
    })();

    return { bind: bind, rebindWithin: rebindWithin, bindWithin: bindWithin };
})();
