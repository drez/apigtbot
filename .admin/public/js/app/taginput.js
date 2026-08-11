/* GoatCheese vanilla tag-input — chip editor for multiple_fenetre columns.
 *
 * Replaces the dead jQuery `tagman` widget. The hidden <input name="<col>">
 * holds the canonical "|id|id|" pipe string the server reads via
 * explode('|', get<Col>()). Initial chip labels come from a data-tag-options
 * {id:name} map (the same gcAcOpts TagQuery the list uses); adding searches the
 * related table via a <col>_search JSON action ({status,count,data:[{show,id}]}).
 * Mirrors app/autocomplete.js for fetch/debounce/menu/keyboard.
 */
window.gcTagInput = (function () {
    'use strict';

    var DEBOUNCE_MS = 250;
    var BLUR_MS = 180; // let a click on a menu row land before blur-resolve

    function isDesktop() {
        return !!(window.matchMedia && window.matchMedia('(min-width: 720px)').matches);
    }

    // "|1|2|" -> ["1","2"]; [] -> ""  ;  ["1","2"] -> "|1|2|"
    function parsePipe(v) {
        return (v || '').split('|').filter(function (s) { return s !== ''; });
    }
    function toPipe(ids) {
        return ids.length ? '|' + ids.join('|') + '|' : '';
    }

    function esc(s) { return window.gcCore.escapeHtml(s); } // #32: shared core

    function fetchResults(url, term) {
        var sep = url.indexOf('?') === -1 ? '?' : '&';
        return fetch(url + sep + 'str=' + encodeURIComponent(term), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.ok ? r.json() : null; })
            .catch(function () { return null; });
    }

    function closeMenu(state) {
        if (state.menu && state.menu.parentNode) { state.menu.parentNode.removeChild(state.menu); }
        if (state.dim && state.dim.parentNode) { state.dim.parentNode.removeChild(state.dim); }
        state.menu = null; state.dim = null; state.items = []; state.active = -1;
    }

    function renderChips(state) {
        // Remove existing chip nodes (keep the text input last).
        var chips = state.box.querySelectorAll('.gc-tag-chip');
        for (var i = 0; i < chips.length; i++) { chips[i].parentNode.removeChild(chips[i]); }
        state.ids.forEach(function (id) {
            var label = state.options[id] != null ? state.options[id] : id;
            var chip = document.createElement('span');
            chip.className = 'gc-tag-chip';
            chip.innerHTML = esc(label) + '<button type="button" class="gc-tag-x" aria-label="Remove">×</button>';
            chip.querySelector('.gc-tag-x').addEventListener('click', function () { removeId(state, id); });
            state.box.insertBefore(chip, state.input);
        });
    }

    function sync(state) {
        state.hidden.value = toPipe(state.ids);
        state.hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function addId(state, id, label) {
        id = String(id);
        if (id === '' || id === '0' || state.ids.indexOf(id) !== -1) { return; }
        state.ids.push(id);
        if (label != null) { state.options[id] = label; }
        renderChips(state);
        sync(state);
    }

    function removeId(state, id) {
        var i = state.ids.indexOf(String(id));
        if (i === -1) { return; }
        state.ids.splice(i, 1);
        renderChips(state);
        sync(state);
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
            results.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'gc-ac-item';
                li.textContent = item.show;
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    addId(state, item.id, item.show);
                    state.input.value = '';
                    closeMenu(state);
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
        state.menu = menu; state.items = results; state.active = -1;
    }

    function highlight(state, idx) {
        if (!state.menu) { return; }
        var lis = state.menu.querySelectorAll('.gc-ac-item');
        if (!lis.length) { return; }
        if (idx < 0) { idx = lis.length - 1; }
        if (idx >= lis.length) { idx = 0; }
        for (var i = 0; i < lis.length; i++) { lis[i].classList.toggle('active', i === idx); }
        state.active = idx;
        lis[idx].scrollIntoView({ block: 'nearest' });
    }

    function runSearch(state, term) {
        fetchResults(state.url, term).then(function (data) {
            if (!data || !data.data) { return; }
            var results = data.data.filter(function (item) {
                return state.ids.indexOf(String(item.id)) === -1;
            });
            if (document.activeElement === state.input) { renderMenu(state, results); }
        });
    }

    function onKeyDown(state, e) {
        var key = e.which || e.keyCode;
        if (key === 40) { e.preventDefault(); if (state.menu) { highlight(state, state.active + 1); } return; }
        if (key === 38) { e.preventDefault(); if (state.menu) { highlight(state, state.active - 1); } return; }
        if (key === 27) { closeMenu(state); return; }
        if (key === 8 && state.input.value === '' && state.ids.length) { // backspace removes last
            removeId(state, state.ids[state.ids.length - 1]); return;
        }
        if (key === 13 || key === 9) { // enter / tab
            if (state.menu && state.active >= 0 && state.items[state.active]) {
                if (key === 13) { e.preventDefault(); }
                var it = state.items[state.active];
                addId(state, it.id, it.show);
                state.input.value = '';
                closeMenu(state);
            }
        }
    }

    function bindOne(box) {
        if (!box || box.getAttribute('data-gc-tag') === '1') { return; }
        box.setAttribute('data-gc-tag', '1');
        var hidden = box.querySelector('input[type=hidden]');
        var input = box.querySelector('.gc-tag-search');
        if (!hidden || !input) { return; }
        var options = {};
        try { options = JSON.parse(box.getAttribute('data-tag-options') || '{}'); } catch (e) { options = {}; }
        var url = box.getAttribute('data-tag-search') || '';
        if (!url) { console.warn('gcTagInput: missing data-tag-search on', box); }
        var state = {
            box: box, hidden: hidden, input: input,
            url: url,
            ids: parsePipe(hidden.value),
            options: options,
            menu: null, dim: null, items: [], active: -1, timer: null, minLength: 1
        };
        renderChips(state);
        input.setAttribute('autocomplete', 'off');
        input.addEventListener('input', function () {
            clearTimeout(state.timer);
            var term = input.value;
            if (term.length < state.minLength) { closeMenu(state); return; }
            state.timer = setTimeout(function () { runSearch(state, term); }, DEBOUNCE_MS);
        });
        input.addEventListener('keydown', function (e) { onKeyDown(state, e); });
        input.addEventListener('blur', function () { setTimeout(function () { closeMenu(state); }, BLUR_MS); });
    }

    function bindWithin(root) {
        (root || document).querySelectorAll('.gc-taginput').forEach(bindOne);
    }

    return { bindOne: bindOne, bindWithin: bindWithin };
})();

if (document.readyState !== 'loading') { window.gcTagInput.bindWithin(document); }
else { document.addEventListener('DOMContentLoaded', function () { window.gcTagInput.bindWithin(document); }); }
