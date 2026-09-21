/* GoatCheese vanilla list client — no jQuery.
 *
 * Drives the card list emitted by the builder
 * (#<Table>ListForm.va-mob.proto-app, rows .va-mob-row).
 * Replicates the server contract (docs/superpowers/specs/server-contract.md
 * §3-5): list/search/sort/page are GET {SITE}{Model} with ui/ms/order/pg
 * and an HTML-fragment response that replaces the #<ui> container inner.
 *
 * The legacy jQuery pipeline (index.js bindEdit/bindPaging/bindSorting) is
 * table-DOM bound and does not drive the card markup; for any container with
 * data-model we strip the legacy-bound pager/sort/search nodes (clone =>
 * drops jQuery handlers) and own them here. Rows have no legacy binding so
 * they use plain delegation. index.js stays loaded for non-card pages until
 * the full A6 client lands.
 */
(function () {
    'use strict';

    // _SITE_URL is declared `let` in an inline page script, so it lives in
    // the global lexical scope, NOT on window. Read it via a typeof guard
    // (a bare reference to an undeclared name would throw).
    var SITE = (typeof _SITE_URL !== 'undefined' && _SITE_URL)
        || (typeof window !== 'undefined' && window._SITE_URL)
        || '/';

    // Sort state keyed by model. The container element is replaced on
    // every fragment swap (innerHTML), so per-element data-* state is
    // lost; this module-level map survives so taps cycle asc→desc→none.
    var sortState = {};

    function qs(el, sel) { return el ? el.querySelector(sel) : null; }
    function qsa(el, sel) { return el ? Array.prototype.slice.call(el.querySelectorAll(sel)) : []; }

    function listContainers() {
        return Array.prototype.slice.call(
            document.querySelectorAll('.va-mob.proto-app[data-model]')
        );
    }

    // The search form (#formMs<Table>) is emitted as a SIBLING of the
    // [data-model] container, not a descendant, so closest() can't reach it.
    // Resolve the owning container by its data-table value instead.
    function containerForTable(table) {
        if (!table) { return null; }
        return document.querySelector(
            '.va-mob.proto-app[data-model][data-table="' + table + '"]'
        );
    }

    // jQuery .serialize() equivalent for the #formMs<Table> search form.
    function serializeSearch(container) {
        var table = container.getAttribute('data-table') || '';
        var form = document.getElementById('formMs' + table)
            || qs(container, '#formMs' + table);
        if (!form) { return ''; }
        var params = new URLSearchParams();
        qsa(form, 'input[name], select[name], textarea[name]').forEach(function (f) {
            if (f.disabled || f.name === '') { return; }
            var type = (f.type || '').toLowerCase();
            if ((type === 'checkbox' || type === 'radio') && !f.checked) { return; }
            if (f.tagName === 'SELECT' && f.multiple) {
                Array.prototype.forEach.call(f.selectedOptions, function (o) {
                    params.append(f.name, o.value);
                });
                return;
            }
            params.append(f.name, f.value);
        });
        return params.toString();
    }

    function destUi(container) {
        return container.getAttribute('data-ui') || 'tabsContain';
    }

    // GET {SITE}{Model} with the list params; swap the #<ui> inner HTML.
    var fetchSeq = {};

    function fetchList(container, extra) {
        var model = container.getAttribute('data-model');
        if (!model) { return; }
        driveLoaderShow(container);
        // Persist the current filter snapshot before fetch — survives
        // reload, navigation, and lets restoreFilters seed the same
        // model's form when the page mounts again.
        var msTable = container.getAttribute('data-table') || '';
        var msForm = document.getElementById('formMs' + msTable);
        if (msForm) { storeFilters(model, msForm); }
        var ui = destUi(container);
        var params = new URLSearchParams();
        params.set('ui', ui);
        var ms = serializeSearch(container);
        if (ms) { params.set('ms', ms); }
        // Child lists are scoped to a parent record: the container carries
        // data-parent (parent class) + data-ip (parent pk). Re-fetch the
        // parent-scoped endpoint (Parent/Child?i=<pk>) so the refresh keeps
        // the parent scope AND honors the child search. Without this the
        // request hits the child's own top-level controller, returning
        // every row unscoped and ignoring the ms filter.
        var parentClass = container.getAttribute('data-parent');
        var parentIp = container.getAttribute('data-ip');
        var path = model;
        if (parentClass && parentIp) {
            path = parentClass + '/' + model;
            params.set('i', parentIp);
        }
        if (extra) {
            Object.keys(extra).forEach(function (k) {
                if (extra[k] !== undefined && extra[k] !== null && extra[k] !== '') {
                    params.set(k, extra[k]);
                }
            });
        }
        var url = SITE + path + '?' + params.toString();
        // Responses can land out of order (search vs sort vs page): only the
        // newest request for a swap region may paint it.
        var seq = fetchSeq[ui] = (fetchSeq[ui] || 0) + 1;
        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) {
            if (r.status === 401) { window.location.reload(); return null; }
            return r.text();
        }).then(function (html) {
            if (html === null || seq !== fetchSeq[ui]) { return; }
            var target = document.getElementById(ui);
            var swapRoot = target || container;
            // Snapshot focus + caret if the user is mid-typing inside the
            // about-to-be-replaced region. innerHTML swap detaches the
            // current input node and blurs it; without restoration the
            // caller has to click the search box again for every keystroke
            // past the debounce window.
            var focusSnap = null;
            var ae = document.activeElement;
            if (ae && swapRoot.contains(ae)
                && (ae.tagName === 'INPUT' || ae.tagName === 'TEXTAREA')) {
                focusSnap = {
                    id: ae.id || '',
                    name: ae.getAttribute('name') || '',
                    // What the user has typed BY NOW — the fragment echoes the
                    // term the request was sent with, which is older.
                    value: ae.value,
                    selStart: null,
                    selEnd: null,
                    dir: null
                };
                try {
                    focusSnap.selStart = ae.selectionStart;
                    focusSnap.selEnd = ae.selectionEnd;
                    focusSnap.dir = ae.selectionDirection || 'none';
                } catch (e) { /* number/email/etc may not expose selection */ }
            }
            if (target) {
                target.innerHTML = html;
            } else {
                container.innerHTML = html;
            }
            // Re-enhance whatever list container now exists.
            listContainers().forEach(enhance);
            // The response re-emits the sw-header mass-action controls with
            // the SAME ids they had before the swap, so whatever relocate()
            // parked in #appTopbarActions is now a stale duplicate — and
            // document.getElementById('mass-action-<Model>') (index.js
            // gcListBulk) is free to resolve to the wrong one. Drop the old
            // relocated nodes and relocate the fresh ones. Guarded on the
            // sw-header actually living inside the region we just replaced,
            // so a child-list swap (data-ui != tabsContain) never removes
            // controls it did not re-render. Idempotent: with nothing tagged
            // the drop is a no-op, and relocate skips nodes already in dst.
            var freshSw = document.querySelector('#tabsContain > .sw-header');
            if (freshSw && swapRoot.contains(freshSw)) {
                dropRelocatedSwHeaderActions();
                relocateSwHeaderActions();
            }
            // The server response re-emits .va-mob-chips + the
            // form#formMs{T} fresh inside #tabsContain — re-lookup
            // the post-swap form (the stale msForm pointer above is
            // detached) and re-sync the funnel/badge/active-filters
            // strip so the user's filter state stays visually
            // represented after the swap.
            var freshForm = document.getElementById('formMs' + msTable);
            if (freshForm) { syncFilterBtn(freshForm); }
            // Restore focus + caret. Prefer id (each search input the
            // emitter renders carries the CamelCase column name as id);
            // fall back to name= when id is empty. Skip silently if the
            // input no longer exists in the new fragment.
            if (focusSnap) {
                var newEl = null;
                if (focusSnap.id) {
                    newEl = document.getElementById(focusSnap.id);
                }
                if (!newEl && focusSnap.name) {
                    newEl = swapRoot.querySelector('[name="' + focusSnap.name.replace(/"/g, '\\"') + '"]');
                }
                if (newEl) {
                    if (newEl.value !== focusSnap.value) { newEl.value = focusSnap.value; }
                    try { newEl.focus({ preventScroll: true }); } catch (e) { try { newEl.focus(); } catch (e2) {} }
                    if (focusSnap.selStart != null) {
                        try { newEl.setSelectionRange(focusSnap.selStart, focusSnap.selEnd, focusSnap.dir || 'none'); }
                        catch (e) { /* type doesn't support setSelectionRange */ }
                    }
                }
            }
            driveLoaderHide();
            // Seam for derived-data consumers (mirrors screens.js
            // refreshChildWrapper): a list was re-fetched.
            try {
                document.dispatchEvent(new CustomEvent('gc:list-refreshed', {
                    detail: { model: model }
                }));
            } catch (e) { /* noop */ }
        }).catch(function () { driveLoaderHide(); /* network error: leave current list intact */ });
    }

    // Row tap -> open the edit screen (full-page nav; A6 adds the push stack).
    function onContainerClick(e) {
        var container = e.target.closest('.va-mob.proto-app[data-model]');
        if (!container) { return; }

        // "+ New" button — emitted as <a class='add-btn' href='.../Model/edit/'>.
        // On desktop we want it to open the same push-screen drawer as a
        // row tap (with empty pk for create mode), not full-page nav.
        // EXCEPT: is_file_upload_table tables get a data-upload-trigger
        // attribute — clicking the row1 button should delegate to the
        // gcUpload-bound element (id stored in the attribute) so the OS
        // file dialog opens instead of a useless edit drawer.
        var addBtn = e.target.closest('.add-btn');
        if (addBtn && container.contains(addBtn)) {
            var uploadId = addBtn.getAttribute('data-upload-trigger');
            if (uploadId) {
                var uploadBtn = document.getElementById(uploadId);
                if (uploadBtn) {
                    e.preventDefault();
                    uploadBtn.click();
                    return;
                }
            }
            if (window.gcScreens && typeof window.gcScreens.openEdit === 'function') {
                e.preventDefault();
                var model = container.getAttribute('data-model');
                // Child lists carry the parent linkage on the container
                // (childListBuilder emits data-ip = parent pk, data-tp =
                // foreign table). Forward it so a new child row is created
                // bound to its parent — replaces the legacy emitted
                // $('#add{Child}').bind('click', …openEdit(child,'',{ip,tp})).
                // Main lists have neither attr, so opts stays empty.
                var addOpts = {};
                var ctxIp = container.getAttribute('data-ip');
                var ctxTp = container.getAttribute('data-tp');
                if (ctxIp) { addOpts.ip = ctxIp; }
                if (ctxTp) { addOpts.tp = ctxTp; }
                window.gcScreens.openEdit(model, '', addOpts);
                return;
            }
            // No push client — fall through to the legacy full-page nav.
        }

        // Sort sheet open/close (the affordance is a bottom sheet of
        // per-column buttons emitted by the generator from real column
        // metadata — see getList.php / InterfaceBuilder listSort).
        var sortBtn = e.target.closest('.va-mob-sort-btn');
        if (sortBtn && container.contains(sortBtn)) {
            e.preventDefault();
            var sheetOpen = qs(container, '.va-mob-sortsheet');
            if (sheetOpen) { sheetOpen.classList.add('open'); }
            return;
        }
        var sortClose = e.target.closest('.va-mob-sortsheet-dim, .va-mob-sortsheet-close');
        if (sortClose && container.contains(sortClose)) {
            e.preventDefault();
            var sh = container.querySelector('.va-mob-sortsheet');
            if (sh) { sh.classList.remove('open'); }
            return;
        }

        // Sort row tap → contract §4. The button carries th='sorted'
        // c=<column>; cycle asc→desc→none via per-container state so a
        // freshly swapped fragment (no attr state) still toggles.
        var th = e.target.closest("[th='sorted']");
        if (th && container.contains(th)) {
            e.preventDefault();
            var col = th.getAttribute('c');
            var model = container.getAttribute('data-model') || '';
            // c='*' is the clear-sort control (chip / "Default order" sheet
            // row, emitted only while a user ordering is stored): the server
            // drops the whole session ordering for this list on the '*'
            // sentinel and the re-rendered fragment comes back chip-less.
            if (col === '*') {
                delete sortState[model];
                var clearSheet = container.querySelector('.va-mob-sortsheet');
                if (clearSheet) { clearSheet.classList.remove('open'); }
                fetchList(container, {
                    order: JSON.stringify({ col: '*', sens: '' })
                });
                return;
            }
            var prev = sortState[model] || { col: null, sens: '' };
            var sens;
            if (prev.col === col) {
                sens = prev.sens === 'asc' ? 'desc' : (prev.sens === 'desc' ? '' : 'asc');
            } else {
                sens = 'asc';
            }
            sortState[model] = { col: col, sens: sens };
            var sheetEl = container.querySelector('.va-mob-sortsheet');
            if (sheetEl) { sheetEl.classList.remove('open'); }
            fetchList(container, {
                order: JSON.stringify({ col: col, sens: (sens || '').toLowerCase() })
            });
            return;
        }

        // Pager prev/next.
        var pg = e.target.closest('[data-direction]');
        if (pg && container.contains(pg)) {
            e.preventDefault();
            var pager = pg.closest('.pagination-wrapper') || container;
            var pageInput = qs(pager, '#page') || qs(container, '#page');
            var current = pageInput ? parseInt(pageInput.value, 10) : 1;
            var total = pageInput ? parseInt(pageInput.getAttribute('data-total'), 10) : 1;
            if (!current || current < 1) { current = 1; }
            if (!total || total < 1) { total = current; }
            if (pg.getAttribute('data-direction') === 'prev') {
                if (current > 1) { current--; }
            } else if (current < total) {
                current++;
            }
            fetchList(container, { pg: current });
            return;
        }

        // Row tap (mobile card). The desktop .dt row is handled by the
        // capture-phase onDtRowCapture below — its cells carry a legacy
        // direct bindEdit click handler that would full-page nav before
        // this document-delegated (bubble) handler runs.
        var row = e.target.closest('.va-mob-row');
        if (!row || !container.contains(row)) { return; }
        var interactive = e.target.closest(
            'a, button, input, select, textarea, label, [j^="delete"], .actionrow'
        );
        if (interactive && interactive !== row && row.contains(interactive)) { return; }

        var rid = row.getAttribute('rid');
        var pk = rid;
        if (rid != null) {
            try {
                var parsed = JSON.parse(rid);
                if (typeof parsed === 'string' || typeof parsed === 'number') {
                    pk = String(parsed);
                }
            } catch (err) { /* non-JSON rid: use raw */ }
        }
        var model = container.getAttribute('data-model');
        // NtN/cross-ref child list: a junction row edits the FAR-SIDE referenced
        // record, not the junction itself (whose composite rid stringifies to
        // "Array"). The emitter stamps data-crmodel=<childRefTable> on NtN child
        // containers (#23 S5 follow-up, restoring the dropped inline __editBound
        // NtN routing); the row's edit cell carries crpk=<far PK>. Re-target.
        var crModel = container.getAttribute('data-crmodel');
        var crRo = false;
        if (crModel) {
            var crCell = row.querySelector("[j^='edit']");
            var crpk = crCell && (crCell.getAttribute('crpk') || crCell.getAttribute('i'));
            // Far record is shared data — open it read-only (server renders
            // via setReadOnly='all' when ro=1; see crossref-readonly spec).
            if (crpk != null && crpk !== '') { model = crModel; pk = crpk; crRo = true; }
        }
        // Prefer the push-screen client (A6) when present; otherwise
        // fall back to a full-page navigation.
        if (window.gcScreens && typeof window.gcScreens.openEdit === 'function') {
            e.preventDefault();
            window.gcScreens.openEdit(model, pk == null ? '' : String(pk), crRo ? { ro: 1 } : undefined);
            return;
        }
        window.location = SITE + model + '/edit/' + encodeURIComponent(pk == null ? '' : pk) + (crRo ? '?ro=1' : '');
    }

    // set_list_threaded: expand a collapsed conversation row in place.
    // Rows carry data-gc-thread / data-gc-thread-count only when their
    // conversation holds more than one message (getList.php's $gcThreadAttr,
    // gated on $gcRowThreadCount > 1) — a single-message row has neither, so
    // there is no toggle badge and this handler is a no-op for it; that
    // row's behavior is unchanged. The expand/collapse control is the
    // visible count badge (data-gc-thread-toggle, same > 1 gate), NOT the
    // row itself — clicking the row still opens the message like any other
    // row; only the badge toggles the conversation.
    // Delegated at document level in the CAPTURE phase, same pattern as
    // onDtRowCapture below (adhoc/cli screens.js documents the analogous
    // `closest('[j^=conglet_][p]')` child-tab delegation) because the list
    // re-renders on sort/filter/paging and per-row listeners would not
    // survive it. Registered BEFORE onDtRowCapture in init() and calling
    // stopImmediatePropagation when it acts: the badge is a dedicated
    // control, so its click must not also open the row via onDtRowCapture.
    function onThreadRowClick(e) {
        var toggle = e.target.closest && e.target.closest('[data-gc-thread-toggle]');
        if (!toggle) { return; }

        var key = toggle.getAttribute('data-gc-thread-toggle');
        var row = toggle.closest('[data-gc-thread]');
        if (!row) { return; }
        var open = row.getAttribute('data-gc-thread-open') === '1';

        e.preventDefault();
        e.stopImmediatePropagation();

        if (open) {
            var n = row.nextElementSibling;
            while (n && n.hasAttribute('data-gc-thread-child')) {
                var next = n.nextElementSibling;
                n.remove();
                n = next;
            }
            row.removeAttribute('data-gc-thread-open');
            return;
        }

        // The model name is not hardcoded: every list container already
        // carries it in data-model (used the same way throughout this file,
        // e.g. onRowActionClick), so no new emitter attribute is needed.
        var container = row.closest('.va-mob.proto-app[data-model]');
        if (!container) { return; }
        var model = container.getAttribute('data-model');
        if (!model) { return; }
        // Thread keys are prefixed ('t:' / 'pk:') and may contain characters
        // that need escaping in a query string.
        var url = SITE + model + '/threadrows?k=' + encodeURIComponent(key);

        // A second click while the rows load inserted the conversation twice.
        if (row.hasAttribute('data-gc-thread-loading')) { return; }
        row.setAttribute('data-gc-thread-loading', '1');
        function loaded() { row.removeAttribute('data-gc-thread-loading'); }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                loaded();
                if (!d || d.error || !d.html) { return; }
                var tpl = document.createElement('tbody');
                tpl.innerHTML = d.html;
                var after = row;
                // Snapshot FIRST: tpl.children is a LIVE HTMLCollection and after.after()
            // moves each element out of it, re-indexing as we go — iterating it
            // directly silently inserts only every second row.
            var kids = Array.prototype.slice.call(tpl.children);
            kids.forEach(function (child, i) {
                    child.setAttribute('data-gc-thread-child', '1');
                    // The last one closes the group visually (CSS draws the
                    // bottom rule on it), so an expanded conversation has a
                    // clear end and does not bleed into the next thread.
                    if (i === kids.length - 1) { child.setAttribute('data-gc-thread-last', '1'); }
                    after.after(child);
                    after = child;
                });
                row.setAttribute('data-gc-thread-open', '1');
            })
            .catch(function () { loaded(); /* leave the row collapsed on failure */ });
    }

    // Desktop .dt-row → push-screen drawer (D3). The row's <td> cells
    // carry a legacy $.fn.bindEdit *direct* click handler that does a
    // full-page document.location to /edit/. A direct handler fires in
    // the bubble phase at the target, before any document-delegated
    // handler — so we must intercept in the CAPTURE phase (document,
    // pre-target) and stopImmediatePropagation so the legacy nav never
    // runs. Falls through to the legacy nav if the push client is absent.
    function onDtRowCapture(e) {
        if (!(window.gcScreens && typeof window.gcScreens.openEdit === 'function')) { return; }
        var container = e.target.closest('.va-mob.proto-app[data-model]');
        if (!container) { return; }
        var row = e.target.closest('.va-dt-row');
        if (!row || !container.contains(row)) { return; }
        // Don't hijack the action cell, delete links, sortable headers,
        // pager, or any interactive control.
        if (e.target.closest(
            "a, button, input, select, textarea, label, [j^=\"delete\"], .actionrow, [th='sorted'], [data-direction]"
        )) { return; }
        var rid = row.getAttribute('rid');
        var pk = rid;
        if (rid != null) {
            try {
                var parsed = JSON.parse(rid);
                if (typeof parsed === 'string' || typeof parsed === 'number') {
                    pk = String(parsed);
                }
            } catch (err) { /* non-JSON rid: use raw */ }
        }
        var model = container.getAttribute('data-model');
        e.preventDefault();
        e.stopImmediatePropagation();
        window.gcScreens.openEdit(model, pk == null ? '' : String(pk));
    }

    // Debounced live search on the search box. Fires SEARCH_DEBOUNCE_MS
    // after the last keystroke; Enter in the box fires immediately.
    var SEARCH_DEBOUNCE_MS = 2000;
    var searchTimer = null;
    // Resolve the list container a search-box event belongs to, or null
    // when the target is not a live-search control.
    function searchContainerFor(box) {
        if (!box || (box.tagName !== 'INPUT' && box.tagName !== 'SELECT' && box.tagName !== 'TEXTAREA')) {
            return null;
        }
        if (box.id === 'page') { return null; }
        // Region 4: every control inside the advanced-filter surface (the
        // name-less SEARCH box, SEARCH-IN / segmented / multi chips, the
        // relocated selects) must NOT trigger the debounced live-search.
        // The surface commits ONLY via explicit Apply (filter.js →
        // gcList.applyFilter). Without this guard the name-less SEARCH box
        // (inside #formMs{T}) would fire premature mid-typing refetches.
        if (box.closest && box.closest('.va-filter-surface')) { return null; }
        var container = box.closest('.va-mob.proto-app[data-model]');
        var searchForm = box.closest('[id^="formMs"]');
        if (!container && searchForm) {
            container = containerForTable(searchForm.id.replace(/^formMs/, ''));
        }
        if (!container) { return null; }
        var inSearch = searchForm
            || box.closest('.va-mob-search')
            || box.getAttribute('j') === 'search';
        return inSearch ? container : null;
    }
    function onSearchInput(e) {
        var container = searchContainerFor(e.target);
        if (!container) { return; }
        // Funnel X-toggle + badge sync happens in the dedicated formMs input
        // listener (registered alongside this one) — calling it here too ran
        // the full-form scan + chip rebuild twice per keystroke.
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            searchTimer = null;
            fetchList(container, { pg: 1 });
        }, SEARCH_DEBOUNCE_MS);
    }
    // Enter in the search box: skip the debounce, search now.
    // ESC in the search box: "Clear all" (every filter, not just the box).
    function onSearchKey(e) {
        if ((e.keyCode !== 13 && e.keyCode !== 27) || e.target.tagName === 'TEXTAREA') { return; }
        var container = searchContainerFor(e.target);
        if (!container) { return; }
        e.preventDefault();
        clearTimeout(searchTimer);
        searchTimer = null;
        if (e.keyCode === 27) {
            var form = e.target.closest('[id^="formMs"]')
                || document.getElementById('formMs' + (container.getAttribute('data-table') || ''));
            if (form) { clearAllFilters(form, container); } else { fetchList(container, { pg: 1 }); }
            return;
        }
        fetchList(container, { pg: 1 });
    }

    function onPageKey(e) {
        if (e.keyCode === 27) {
            var openSheet = document.querySelector('.va-mob-sortsheet.open');
            if (openSheet) {
                e.preventDefault();
                e.stopPropagation();
                openSheet.classList.remove('open');
            }
            return;
        }
        if (e.keyCode !== 13) { return; }
        var input = e.target;
        if (!input.matches('#page')) { return; }
        var container = input.closest('.va-mob.proto-app[data-model]');
        if (!container) { return; }
        e.preventDefault();
        var total = parseInt(input.getAttribute('data-total'), 10) || 1;
        var n = parseInt(input.value, 10) || 1;
        if (n < 1) { n = 1; }
        if (n > total) { n = total; }
        fetchList(container, { pg: n });
    }

    // Guideline single-field search (06-countries-list): the first
    // .ac-search-item is the visible box; .va-mob-filter-btn is a filter
    // toggle when the box is empty (reveals the collapsed advanced
    // fields) and a clear button when it has a value (empties + refetch).
    // Region 4: the always-visible inline box is the FIRST text-like
    // search column's existing input, now wrapped in
    // .va-mob-search-inline (the rest of the controls live inside the
    // .va-filter-surface blocks). Target the inline wrapper so the
    // clear-button mode (data-mode='clear') still finds it. Fall back to
    // the legacy .ac-search-item lookup for any form without the wrapper
    // (defensive — every Region-4 form has it).
    function firstSearchBox(form) {
        if (!form) { return null; }
        return form.querySelector(
            '.va-mob-search-inline input:not([type="hidden"]),'
            + ' .va-mob-search-inline select, .va-mob-search-inline textarea'
        ) || form.querySelector(
            '.ac-search-item input:not([type="hidden"]),'
            + ' .ac-search-item select, .ac-search-item textarea'
        );
    }
    // Walk the form, collect each non-default filter as { name, val }
    // for the active-filters chip strip + the funnel badge count.
    function collectActiveFilters(form) {
        if (!form) { return []; }
        var out = [];
        var seen = {};
        var inputs = form.querySelectorAll('input, select, textarea');
        // An FK autocomplete is a text input (j='autocomplete', shows the human
        // label) paired with a hidden id field named by its rid (IdCompanyAutoc
        // + IdCompany[]). Render ONE chip — the human label under the base field
        // name — and skip the raw id field so we don't get a second
        // "Company: 1002" chip beside it.
        var ownedHidden = {};
        Array.prototype.forEach.call(inputs, function (inp) {
            if (inp.getAttribute && inp.getAttribute('j') === 'autocomplete') {
                var rid = inp.getAttribute('rid');
                if (rid) { ownedHidden[rid] = true; }
            }
        });
        Array.prototype.forEach.call(inputs, function (inp) {
            if (inp.type === 'submit' || inp.type === 'button') { return; }
            if (inp.name === 'csrf' || inp.name === 'Seq' || inp.id === 'page' || inp.id === 'ui') { return; }
            if (!inp.name) { return; }
            if (ownedHidden[inp.name]) { return; }   // rendered by its Autoc partner
            var isAutoc = inp.getAttribute && inp.getAttribute('j') === 'autocomplete';
            var raw = inp.name.replace(/\[\]$/, '');
            var clearField = inp.name;
            if (isAutoc) {
                raw = raw.replace(/Autoc$/, '');      // IdCompanyAutoc -> IdCompany -> "Company"
                var rid = inp.getAttribute('rid');
                clearField = inp.name + (rid ? ',' + rid : '');
                // A real selection writes the id into the hidden; if it's empty
                // nothing is actually filtered, so don't show a stray chip.
                if (rid) {
                    var hid = form.querySelector('[name="' + rid + '"]');
                    if (hid && String(hid.value || '').trim() === '') { return; }
                }
            }
            if (seen[raw + '|' + inp.value]) { return; }
            var v;
            if (inp.tagName === 'SELECT' && inp.multiple) {
                v = Array.prototype.filter
                    .call(inp.options, function (o) { return o.selected && o.value && o.value !== 'default'; })
                    .map(function (o) { return o.textContent.trim(); }).join(', ');
            } else {
                v = String(inp.value || '').trim();
            }
            if (v === '' || v === 'default') { return; }
            seen[raw + '|' + inp.value] = true;
            out.push({ name: raw, label: humanize(raw), val: v, field: clearField, el: inp });
        });
        return out;
    }
    function humanize(name) {
        // "IdCountry" -> "Country", "Status" -> "Status", "PhoneMobile" -> "Phone Mobile"
        var n = String(name || '').replace(/^Id/, '');
        n = n.replace(/([a-z])([A-Z])/g, '$1 $2');
        return n;
    }
    // Render an active-filters chip strip into the .va-mob-chips
    // sibling of the search form. Each chip carries data-clear-name
    // so a click on the × removes just that filter. A "Clear all"
    // button is appended.
    function renderActiveFilters(form, filters) {
        var container = null;
        var protoApp = form.closest('.va-mob.proto-app');
        if (protoApp) { container = protoApp.querySelector('.va-mob-chips'); }
        if (!container) { return; }
        // Stash the original preset chips on first run so we can
        // swap back when filters clear.
        if (!container.__gcPresetsHtml) {
            container.__gcPresetsHtml = container.innerHTML;
        }
        if (!filters.length) {
            // Restore presets (preserving any .active class the user
            // had on a chip). Cheap re-render is fine.
            container.classList.remove('active-filters');
            container.innerHTML = container.__gcPresetsHtml;
            return;
        }
        container.classList.add('active-filters');
        var html = filters.map(function (f) {
            var modCls = 'cl-active-filter';
            // Status filter chip inherits its followup state's tint —
            // map common status values to a colour family the css
            // already understands (pill-won, pill-lost, etc.).
            if (/^Status$/i.test(f.name)) {
                var slug = String(f.val).toLowerCase().replace(/[^a-z0-9]+/g, '-');
                modCls += ' cl-active-filter--status pill-' + slug;
            }
            return '<button type="button" class="' + modCls + '" data-clear-name="'
                + escapeAttr(f.field) + '">'
                + '<span class="cl-active-filter-label">' + escapeHtml(f.label) + ':</span> '
                + '<span class="cl-active-filter-val">' + escapeHtml(f.val) + '</span>'
                + '<span class="cl-active-filter-x" aria-hidden="true">×</span>'
                + '</button>';
        }).join('');
        html += '<button type="button" class="cl-active-filter-clear" data-clear-all="1">'
            + 'Clear all</button>';
        container.innerHTML = html;
    }
    function escapeHtml(s) { return window.gcCore.escapeHtml(s); } // #32: shared core
    function escapeAttr(s) { return escapeHtml(s); }

    // Update the inline search input placeholder to reflect which
    // text fields are being searched. List 1-2 fields by name, then
    // ellipsize; show "Search all fields…" if every text field is in.
    function updateSearchPlaceholder(form) {
        if (!form) { return; }
        var box = form.querySelector('.va-mob-search input:not([type="hidden"]),'
            + ' .va-mob-search-inline input:not([type="hidden"]),'
            + ' .ac-search-item input:not([type="hidden"])');
        if (!box) { return; }
        var texts = Array.prototype.filter.call(
            form.querySelectorAll('input[type="text"], input:not([type])'),
            function (i) { return i.name && i.type !== 'hidden'; }
        );
        var names = texts.map(function (i) { return humanize(i.name).toLowerCase(); });
        // Dedup + filter empties
        names = names.filter(function (n, i) { return n && names.indexOf(n) === i; });
        var ph;
        if (names.length >= 5) {
            ph = 'Search all fields…';
        } else if (names.length >= 3) {
            ph = 'Search ' + names.slice(0, 3).join(', ') + '…';
        } else if (names.length >= 1) {
            ph = 'Search ' + names.join(', ') + '…';
        } else {
            ph = 'Search…';
        }
        box.setAttribute('placeholder', ph);
    }

    // Filter state persistence — store the form's filter snapshot in
    // localStorage so reloading the same model restores it.
    // ----------------------------------------------------------------
    var FILTER_STORE_PREFIX = 'gc.filter.';
    function snapshotFilters(form) {
        if (!form) { return null; }
        var snap = {};
        var inputs = form.querySelectorAll('input, select, textarea');
        Array.prototype.forEach.call(inputs, function (inp) {
            if (inp.type === 'submit' || inp.type === 'button') { return; }
            if (inp.name === 'csrf' || inp.name === 'Seq' || inp.id === 'page' || inp.id === 'ui') { return; }
            if (!inp.name) { return; }
            if (inp.tagName === 'SELECT' && inp.multiple) {
                var vals = Array.prototype.filter
                    .call(inp.options, function (o) { return o.selected && o.value !== 'default'; })
                    .map(function (o) { return o.value; });
                if (vals.length) { snap[inp.name] = vals; }
            } else if (inp.type === 'checkbox' || inp.type === 'radio') {
                if (inp.checked) { snap[inp.name] = inp.value || '1'; }
            } else {
                var v = String(inp.value || '').trim();
                if (v !== '' && v !== 'default') { snap[inp.name] = v; }
            }
        });
        return snap;
    }
    function storeFilters(model, form) {
        if (!model || !form) { return; }
        try {
            var snap = snapshotFilters(form);
            if (snap && Object.keys(snap).length) {
                localStorage.setItem(FILTER_STORE_PREFIX + model, JSON.stringify(snap));
            } else {
                localStorage.removeItem(FILTER_STORE_PREFIX + model);
            }
        } catch (e) { /* private browsing / disabled storage */ }
    }
    function restoreFilters(model, form) {
        if (!model || !form) { return false; }
        var raw;
        try { raw = localStorage.getItem(FILTER_STORE_PREFIX + model); } catch (e) { return false; }
        if (!raw) { return false; }
        var snap;
        try { snap = JSON.parse(raw); } catch (e) { return false; }
        if (!snap || typeof snap !== 'object') { return false; }
        var dirty = false;
        Object.keys(snap).forEach(function (name) {
            var val = snap[name];
            var el = form.querySelector('[name="' + name + '"]');
            if (!el) { return; }
            if (el.tagName === 'SELECT' && el.multiple && Array.isArray(val)) {
                Array.prototype.forEach.call(el.options, function (o) {
                    o.selected = val.indexOf(o.value) !== -1;
                });
                dirty = true;
            } else if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = true;
                dirty = true;
            } else {
                el.value = String(val);
                dirty = true;
            }
        });
        return dirty;
    }

    function syncFilterBtn(form) {
        if (!form) { return; }
        var btn = form.querySelector('.va-mob-filter-btn');
        if (!btn) { return; }
        // X-toggle reflects whether ANY filter is set, not just the
        // inline search box. collectActiveFilters() is the single
        // source of truth here: it sees the filter state the v2
        // widgets keep in hidden inputs (selectbox/segmented values,
        // autocomplete id fields), skips the infrastructure hiddens
        // (csrf/Seq/page/ui), and counts an autocomplete text+id pair
        // as one filter. The previous hand-rolled scan skipped ALL
        // hidden inputs, so widget-held filters never flipped the
        // funnel to clear mode or counted in the badge.
        var filters = collectActiveFilters(form);
        var hasFilter = filters.length > 0;
        btn.setAttribute('data-mode', hasFilter ? 'clear' : 'filter');
        var ic = btn.querySelector('i');
        if (ic) {
            ic.className = hasFilter ? 'ri-close-circle-fill' : 'ri-filter-3-line';
        }
        // Active-filters chip strip — swap the quick-presets row for
        // a wrap of dismissable chips + Clear all when any filter
        // is set. Always-running placeholder update.
        renderActiveFilters(form, filters);
        updateSearchPlaceholder(form);

        // Filter badge: small count chip pinned on the funnel icon
        // when any filter is active.
        var badge = btn.querySelector('.filter-badge');
        if (hasFilter) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'filter-badge';
                btn.appendChild(badge);
            }
            badge.textContent = String(filters.length);
        } else if (badge) {
            badge.remove();
        }
    }
    // Clear ALL filters in a #formMs<T> form, not just the inline box.
    // Walks every editable input/select/textarea and resets it to its
    // default, then re-fetches page 1. Shared by the funnel's clear
    // mode, the chip strip's "Clear all" and ESC in the search box.
    function clearAllFilters(form, container) {
        // Walk every editable input/select/textarea and reset to
        // its default. This implements the "Clear all" requirement
        // (previously only emptied the first search box).
        var fields = form.querySelectorAll('input, select, textarea');
        Array.prototype.forEach.call(fields, function (inp) {
            if (inp.type === 'submit' || inp.type === 'button') { return; }
            // Only the infrastructure fields survive a clear. The
            // other hidden inputs CARRY filter state in the v2 UI
            // (selectbox/segmented values, autocomplete id fields)
            // — skipping all hiddens left FK/select filters silently
            // active after "Clear all" while the chips disappeared.
            if (inp.name === 'csrf' || inp.name === 'Seq' || inp.id === 'page' || inp.id === 'ui') { return; }
            if (inp.tagName === 'SELECT') {
                Array.prototype.forEach.call(inp.options, function (o, i) {
                    o.selected = (i === 0);
                });
                try { inp.dispatchEvent(new Event('change', { bubbles: true })); } catch (er) {}
            } else if (inp.type === 'checkbox' || inp.type === 'radio') {
                inp.checked = false;
            } else {
                inp.value = '';
                // Hidden inputs back widgets (selectbox label, filter
                // sheet controls) — let them hear the reset.
                if (inp.type === 'hidden') {
                    try { inp.dispatchEvent(new Event('change', { bubbles: true })); } catch (er) {}
                }
            }
        });
        syncFilterBtn(form);
        if (container) { fetchList(container, { pg: 1 }); }
    }
    function onFilterBtn(e) {
        var btn = e.target.closest && e.target.closest('.va-mob-filter-btn');
        if (!btn) { return; }
        var form = btn.closest('[id^="formMs"]');
        if (!form) { return; }
        e.preventDefault();
        var container = containerForTable(form.id.replace(/^formMs/, ''));
        if (btn.getAttribute('data-mode') === 'clear') {
            clearAllFilters(form, container);
        } else if (window.gcFilter && typeof window.gcFilter.open === 'function') {
            // Region 4: open the advanced-filter surface (replaces the
            // Task-1 .is-expanded interim). filter.js owns the sheet.
            window.gcFilter.open(form);
        }
    }

    // Active-filters chip dismissal — clicks on .cl-active-filter
    // remove just that filter; Clear all clears the whole form.
    function onActiveFilterClick(e) {
        var clearAll = e.target.closest && e.target.closest('.cl-active-filter-clear');
        var chip = e.target.closest && e.target.closest('.cl-active-filter');
        if (!clearAll && !chip) { return; }
        var chipsHost = (clearAll || chip).closest('.va-mob-chips');
        if (!chipsHost) { return; }
        var protoApp = chipsHost.closest('.va-mob.proto-app[data-model]');
        var table = protoApp ? (protoApp.getAttribute('data-table') || '') : '';
        var form = document.getElementById('formMs' + table);
        if (!form) { return; }
        e.preventDefault();
        if (clearAll) {
            // Re-use the funnel's clear-mode path (zero every editable
            // field, re-fetch, re-sync the chip strip).
            clearAllFilters(form, protoApp || containerForTable(table));
            return;
        }
        // Remove just this filter. data-clear-name may carry more than one
        // field (an autocomplete chip clears both its text input and the
        // hidden id field), comma-separated.
        var fname = chip.getAttribute('data-clear-name') || '';
        fname.split(',').forEach(function (nm) {
            nm = nm.trim();
            if (!nm) { return; }
            var el = form.querySelector('[name="' + nm + '"]');
            if (!el) { return; }
            if (el.tagName === 'SELECT') {
                Array.prototype.forEach.call(el.options, function (o, i) { o.selected = (i === 0); });
                try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (er) {}
            } else if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = false;
            } else {
                el.value = '';
            }
        });
        syncFilterBtn(form);
        var container = containerForTable(table);
        if (container) { fetchList(container, { pg: 1 }); }
    }

    // Guideline filter chips (06-countries-list): a chip sets the same
    // #formMs{T} field its source ENUM <select> drives, then re-fetches —
    // the `ms` contract is unchanged (server re-serializes the form).
    function onChipClick(e) {
        var chip = e.target.closest && e.target.closest('.va-mob-chip');
        if (!chip) { return; }
        var field = chip.getAttribute('data-msfield');
        if (!field) { return; }
        e.preventDefault();
        var chips = chip.closest('.va-mob-chips');
        var table = '';
        var form = null;
        if (chips) {
            var cont = chips.closest('.va-mob.proto-app[data-model]');
            table = cont ? (cont.getAttribute('data-table') || '') : '';
        }
        form = document.getElementById('formMs' + table)
            || (chips && chips.parentNode
                ? chips.parentNode.querySelector('[id^="formMs"]') : null);
        if (!form) { return; }
        var val = chip.getAttribute('data-msval') || '';
        var sel = form.querySelector(
            'select[name="' + field + '[]"], select[name="' + field + '"]'
        );
        if (sel) {
            Array.prototype.forEach.call(sel.options, function (o) {
                o.selected = (val !== '' && o.value === val);
            });
        } else {
            var inp = form.querySelector(
                '[name="' + field + '[]"], [name="' + field + '"]'
            );
            if (inp) { inp.value = val; }
        }
        if (chips) {
            qsa(chips, '.va-mob-chip').forEach(function (c) {
                c.classList.toggle('active', c === chip);
            });
        }
        var container = containerForTable(table);
        if (container) { fetchList(container, { pg: 1 }); }
    }

    // Paint the active-sort marker. The CSS arrow (_desktopv2.scss /
    // _listv2.scss: th[sens] / .va-mob-sortrow[sens]) is driven purely by
    // the `sens` attribute. The server only sets it via an inline <script>
    // (orderReadyJsOrder) that does NOT run on innerHTML swaps, so the
    // vanilla client owns it here: clear every sortable header, then stamp
    // sens on the one(s) matching the authoritative sortState[model].
    // Keys on `c` — the same attribute the click handler + sortState use;
    // covers desktop <th>, the mobile sort-sheet buttons, and child lists
    // (each child container has its own data-model) in one pass.
    function applySortMarker(container) {
        if (!container) { return; }
        var model = container.getAttribute('data-model') || '';
        var st = sortState[model] || { col: null, sens: '' };
        qsa(container, "[th='sorted']").forEach(function (el) {
            if (st.col && st.sens && el.getAttribute('c') === st.col) {
                el.setAttribute('sens', st.sens);
            } else {
                el.removeAttribute('sens');
            }
        });
    }

    // ---- Quick-filter chip rows: collapsible per column, state remembered ----
    // set_search_chips emits one .va-mob-chips row per column (Verdict,
    // Priority, Category, ...), which eats a lot of vertical space on a list
    // the user knows by heart. Each row gets its own toggle, injected
    // client-side (no emitter change), and its own collapsed flag in
    // localStorage keyed model+column, so the layout comes back the way the
    // user left it after a reload or a fragment swap. Rows are DIRECT
    // children of .va-mob — ':scope >' keeps a nested child list's chips out
    // of the parent's toggles.
    function chipRows(container) { return qsa(container, ':scope > .va-mob-chips'); }
    // The column a row filters on. The FIRST row is re-purposed by
    // renderActiveFilters (innerHTML swap) once any filter is set, so the
    // field is stashed on the element while it is still a preset row.
    function chipRowField(row) {
        var c = qs(row, '[data-msfield]');
        if (c) { row.__gcChipField = c.getAttribute('data-msfield') || ''; }
        return row.__gcChipField || '';
    }
    function chipsKey(model, field) { return 'gc.chips.collapsed.' + (model || '') + '.' + field; }
    function chipsCollapsed(model, field) {
        try { return localStorage.getItem(chipsKey(model, field)) === '1'; } catch (e) { return false; }
    }
    function storeChipsCollapsed(model, field, on) {
        try { localStorage.setItem(chipsKey(model, field), on ? '1' : '0'); } catch (e) { /* disabled */ }
    }
    // What this row currently selects, so collapsing never hides the fact
    // that the filter is on. A chip with an empty data-msval is the "All" reset.
    function chipRowSummary(row) {
        return qsa(row, '.va-mob-chip.active[data-msval]')
            .filter(function (c) { return c.getAttribute('data-msval'); })
            .map(function (c) { return (c.textContent || '').trim(); })
            .join(', ');
    }
    function applyChipRowState(row, strip, collapsed) {
        // The flag is unconditional; the CSS keeps the row visible while it
        // carries .active-filters (renderActiveFilters re-purposes the FIRST
        // chip row as the whole list's filter summary, and hiding that would
        // hide live state + the Clear all). Doing it in CSS rather than here
        // makes it independent of which of the two runs last on a swap.
        row.classList.toggle('gc-chips-hidden', !!collapsed);
        var btn = qs(strip, 'button');
        if (!btn) { return; }
        btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        var ic = qs(btn, 'i');
        if (ic) { ic.className = collapsed ? 'ri-arrow-right-s-line' : 'ri-arrow-down-s-line'; }
        var sum = qs(strip, '.va-mob-chips-toggle-sum');
        if (sum) { sum.textContent = collapsed ? chipRowSummary(row) : ''; }
    }
    function setupChipsToggles(container) {
        var model = container.getAttribute('data-model') || '';
        chipRows(container).forEach(function (row) {
            var field = chipRowField(row);
            if (!field) { return; }
            var strip = row.previousElementSibling;
            if (!strip || !strip.classList.contains('va-mob-chips-toggle')) {
                strip = document.createElement('div');
                strip.className = 'va-mob-chips-toggle';
                strip.innerHTML = "<button type='button' aria-expanded='true'>"
                    + "<i class='ri-arrow-down-s-line' aria-hidden='true'></i>"
                    + "<span class='va-mob-chips-toggle-label'></span>"
                    + "<span class='va-mob-chips-toggle-sum'></span></button>";
                qs(strip, '.va-mob-chips-toggle-label').textContent = humanize(field);
                row.parentNode.insertBefore(strip, row);
                strip.firstChild.addEventListener('click', function () {
                    var now = !row.classList.contains('gc-chips-hidden');
                    storeChipsCollapsed(model, field, now);
                    applyChipRowState(row, strip, now);
                }, false);
            }
            applyChipRowState(row, strip, chipsCollapsed(model, field));
        });
    }

    // Strip legacy jQuery handlers from pager/sort/search nodes by replacing
    // them with clones (clones carry no jQuery event cache). Rows are not
    // legacy-bound so delegation alone is enough for them.
    function enhance(container) {
        if (container.getAttribute('data-vlist') === '1') { return; }
        container.setAttribute('data-vlist', '1');
        var table = container.getAttribute('data-table') || '';
        var strip = function (node) {
            if (node && node.parentNode) {
                node.parentNode.replaceChild(node.cloneNode(true), node);
            }
        };
        // Pager / sort headers live inside the container.
        ['#' + (container.getAttribute('data-model') || '') + 'Pager',
            "[th='sorted']"].forEach(function (sel) {
            if (!sel || sel === '#') { return; }
            qsa(container, sel).forEach(strip);
        });
        // The search form is a sibling of the container (document-wide).
        if (table) {
            var f = document.getElementById('formMs' + table);
            strip(f);
            // Restore prior filter snapshot for this model — ONCE per
            // model per page session. After the AJAX response from
            // restore→fetchList replaces #tabsContain, enhance runs
            // again on the new container; without this guard we'd
            // loop or overwrite the freshly-rendered chips. The
            // sessionStorage key resets on each full page nav.
            var model = container.getAttribute('data-model');
            try {
                var restoreFlag = 'gc.filter.restored.' + (model || '');
                if (model && f && !sessionStorage.getItem(restoreFlag)) {
                    sessionStorage.setItem(restoreFlag, '1');
                    var restored = restoreFilters(model, f);
                    if (restored) {
                        fetchList(container, { pg: 1 });
                    }
                }
            } catch (e) { /* sessionStorage disabled */ }
            syncFilterBtn(f);
        }
        setupChipsToggles(container);
        // Seed sort state from the server-rendered marker on first mount
        // (initial full-page load runs the emitted orderReadyJsOrder inline
        // script, which sets sens=; AJAX swaps don't, so we read it once and
        // re-apply it ourselves thereafter). On a post-sort re-enhance,
        // sortState[model] is already set by the click handler, so the seed
        // is skipped and applySortMarker just re-paints the active header.
        var smModel = container.getAttribute('data-model') || '';
        if (!sortState[smModel]) {
            // Prefer the declarative data-gc-sort='Col:Dir' (child lists, #23 S5 —
            // off the re-exec arm). Fall back to the server-rendered
            // [th='sorted'][sens] marker (top-level lists still seed it via their
            // native page-load onReadyJs).
            var dgs = container.getAttribute('data-gc-sort') || '';
            var dgsParts = dgs ? dgs.split(':') : [];
            if (dgsParts.length === 2 && dgsParts[0]) {
                sortState[smModel] = { col: dgsParts[0], sens: (dgsParts[1] || '').toLowerCase() };
            } else {
                var seeded = qs(container, "[th='sorted'][sens]");
                if (seeded) {
                    sortState[smModel] = {
                        col: seeded.getAttribute('c'),
                        sens: seeded.getAttribute('sens')
                    };
                }
            }
        }
        applySortMarker(container);
    }

    // Move list-page action controls (mass-action select, bulk-update btn,
    // un/check-all toggle, etc.) out of the legacy #tabsContain > .sw-header
    // (which the v2 design hides because it collides with .app-topbar) and
    // into the topbar's right-side actions slot (#appTopbarActions). The
    // controls keep their ids/names so existing jQuery handlers still bind
    // — we only reparent them. Skipped: toggle-menu (rail covers it) and
    // the inner #add{Table} (.va-mob-row1 .add-btn already triggers New).
    // Row checkbox label click → toggle the SIBLING input.
    // The codegen emits two copies of `<input id="check_N">` (mobile card
    // + desktop table) — same id. Browser's native label[for=] toggles
    // the FIRST id, which is the hidden mobile one, so the visible
    // desktop checkbox never flips. Bypass that by toggling the adjacent
    // input directly. Capture phase + preventDefault stops the native
    // behavior. Idempotent.
    function onRowCheckboxLabelClick(e) {
        var lbl = e.target.closest && e.target.closest('.actionrow label[for^="check_"]');
        if (!lbl) { return; }
        var input = lbl.previousElementSibling;
        if (!input || input.tagName !== 'INPUT' || input.type !== 'checkbox') { return; }
        if (!/^check_multi_/.test(input.getAttribute('j') || '')) { return; }
        e.preventDefault();
        e.stopPropagation();
        input.checked = !input.checked;
        input.dispatchEvent(new Event('click', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Every node relocate() moves into the topbar is tagged with this
    // attribute, so a later list swap can drop exactly the nodes we put
    // there and nothing else the page owns.
    var SW_RELOCATED_ATTR = 'data-gc-sw-relocated';

    function relocateSwHeaderActions() {
        var dst = document.getElementById('appTopbarActions');
        if (!dst) { return; }
        var sw = document.querySelector('#tabsContain > .sw-header');
        if (!sw) { return; }
        var selectors = [
            '.mass-action-sel',
            '#bulkUpdateForm',
            '.mass-action-toggle',
            'input[id^="mass-action-"]',
            '.custom-controls > a.header-controls',
            // Opt-in for a behavior's own list-header control (e.g. with_stripe
            // "Sync prices"): .default-controls is hidden in the v2 layout.
            '.gc-topbar-action'
        ].join(',');
        Array.prototype.forEach.call(
            sw.querySelectorAll(selectors),
            function (el) {
                // Mass-action checkbox + its <label> belong together —
                // append the label right after we move the input so the
                // for= pairing stays adjacent in the topbar.
                if (el.parentNode === dst) { return; }
                el.setAttribute(SW_RELOCATED_ATTR, '1');
                dst.appendChild(el);
            }
        );
    }

    // Remove the controls a previous relocate() parked in the topbar. Only
    // ever called right after a swap re-emitted fresh copies of them inside
    // #tabsContain — otherwise it would delete the only copy there is.
    function dropRelocatedSwHeaderActions() {
        var dst = document.getElementById('appTopbarActions');
        if (!dst) { return; }
        Array.prototype.forEach.call(
            dst.querySelectorAll('[' + SW_RELOCATED_ATTR + ']'),
            function (el) {
                if (el.parentNode) { el.parentNode.removeChild(el); }
            }
        );
    }

    // ---- is_drive_backed folder browser -----------------------------
    // Drive lists carry data-drive="1" and data-dpath="<current sub-path>".
    // Folder rows + breadcrumbs carry data-dpath; the toolbar buttons carry
    // data-drive-action. Navigation reuses fetchList (XHR fragment swap), so
    // the server re-renders the list at the new dpath.
    function driveContainer(el) {
        return el && el.closest
            ? el.closest('.va-mob.proto-app[data-model][data-drive]') : null;
    }

    // Loading overlay for slow Drive (Google API) actions. Fixed-position so
    // it survives the fetchList innerHTML swap; non-counting show/hide — each
    // async chain hides once at its end (fetchList settle) or in its catch.
    var _driveOverlay = null;
    function ensureDriveLoaderCss() {
        if (document.getElementById('gc-drive-loader-css')) { return; }
        var st = document.createElement('style');
        st.id = 'gc-drive-loader-css';
        st.textContent =
            '.gc-drive-loading{position:fixed;z-index:60;display:flex;align-items:center;'
          + 'justify-content:center;background:rgba(255,255,255,.55);pointer-events:all;}'
          + '.gc-drive-spinner{width:34px;height:34px;border:3px solid #cfd6dd;'
          + 'border-top-color:#008bc5;border-radius:50%;animation:gcdrivespin .7s linear infinite;}'
          + '@keyframes gcdrivespin{to{transform:rotate(360deg);}}';
        document.head.appendChild(st);
    }
    function driveLoaderShow(container) {
        if (!container || container.getAttribute('data-drive') !== '1') { return; }
        ensureDriveLoaderCss();
        if (!_driveOverlay) {
            _driveOverlay = document.createElement('div');
            _driveOverlay.className = 'gc-drive-loading';
            _driveOverlay.innerHTML = '<span class="gc-drive-spinner"></span>';
            document.body.appendChild(_driveOverlay);
        }
        var r = container.getBoundingClientRect();
        _driveOverlay.style.left = r.left + 'px';
        _driveOverlay.style.top = r.top + 'px';
        _driveOverlay.style.width = r.width + 'px';
        _driveOverlay.style.height = r.height + 'px';
        _driveOverlay.style.display = 'flex';
    }
    function driveLoaderHide() {
        if (_driveOverlay) { _driveOverlay.style.display = 'none'; }
    }
    function onDriveNav(e) {
        // Only breadcrumbs and folder rows navigate. Match those
        // explicitly — NOT a bare [data-dpath], because the container
        // itself carries data-dpath (current path); a bare match would
        // hijack every click inside it (toolbar buttons, the Add File
        // label) with preventDefault + a refetch.
        // Row action buttons (rename/move/delete) sit inside folder rows;
        // a click on one must NOT navigate into the folder. Bail before the
        // folder-row match so onDriveAction handles it instead.
        if (e.target.closest && e.target.closest('[data-drive-action]')) { return; }
        var nav = e.target.closest && e.target.closest('.va-crumb[data-dpath], .va-drive-folder[data-dpath]');
        if (!nav) { return; }
        var container = driveContainer(nav);
        if (!container) { return; }
        e.preventDefault();
        fetchList(container, { dpath: nav.getAttribute('data-dpath') || '', pg: 1 });
    }

    // Shared: POST a new folder into the current scope, then refresh.
    function driveCreateFolder(container, model, dpath, name) {
        name = (name || '').trim();
        if (!name) { return; }
        driveLoaderShow(container);
        var body = new URLSearchParams();
        body.set('name', name);
        body.set('dpath', dpath);
        fetch(SITE + model + '/newFolder', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        }).then(function (r) { return r.json().catch(function () { return {}; }); })
          .then(function (res) {
              if (res && res.ok === false && res.error) { alertb('Error', res.error); } // #no-native-alert
              fetchList(container, { dpath: dpath });
          }).catch(function () { driveLoaderHide(); /* network error: leave list intact */ });
    }

    // Toolbar actions. Add File is a native <label> wrapping the file
    // input (no programmatic .click(), which is unreliable on a hidden
    // input). New Folder reveals an inline name field instead of a
    // browser prompt; Create/Cancel and Enter drive it.
    function onDriveAction(e) {
        var btn = e.target.closest && e.target.closest('[data-drive-action]');
        if (!btn) { return; }
        var container = driveContainer(btn);
        if (!container) { return; }
        var action = btn.getAttribute('data-drive-action');
        var model = container.getAttribute('data-model');
        var dpath = container.getAttribute('data-dpath') || '';
        var nf = container.querySelector('.va-drive-newfolder');
        if (action === 'newfolder') {
            e.preventDefault();
            if (nf) {
                var show = nf.style.display === 'none' || nf.style.display === '';
                nf.style.display = show ? 'flex' : 'none';
                if (show) {
                    var fi = nf.querySelector('[data-drive-nf-input]');
                    if (fi) { fi.value = ''; fi.focus(); }
                }
            }
            return;
        }
        if (action === 'newfolder-create') {
            e.preventDefault();
            var inp = nf ? nf.querySelector('[data-drive-nf-input]') : null;
            driveCreateFolder(container, model, dpath, inp ? inp.value : '');
            return;
        }
        if (action === 'newfolder-cancel') {
            e.preventDefault();
            if (nf) {
                nf.style.display = 'none';
                var ci = nf.querySelector('[data-drive-nf-input]');
                if (ci) { ci.value = ''; }
            }
            return;
        }
        if (action === 'delete') {
            e.preventDefault();
            var row = btn.closest('.va-drive-row');
            if (!row) { return; }
            var delId = row.getAttribute('data-id');
            if (!delId) { return; }
            var delName = row.getAttribute('data-name') || 'this item';
            var sendDelete = function () {
                driveLoaderShow(container);
                var body = new URLSearchParams();
                // Drive ids are alphanumeric strings; the server json_decode()s
                // request['i'], so JSON-encode it (matches moveOne/deleteOne).
                body.set('i', JSON.stringify(delId));
                fetch(SITE + model + '/delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString()
                }).then(function () {
                    if (window.gcScreens && window.gcScreens.toast) {
                        window.gcScreens.toast('Deleted');
                    }
                    fetchList(container, { dpath: dpath });
                }).catch(function () {
                    driveLoaderHide();
                    if (window.gcScreens && window.gcScreens.toast) {
                        window.gcScreens.toast('Delete failed');
                    }
                });
            };
            if (window.gcScreens && typeof window.gcScreens.confirm === 'function') {
                window.gcScreens.confirm('Delete "' + delName + '"? This cannot be undone.', { confirmLabel: 'Delete', danger: true })
                    .then(function (ok) { if (ok) { sendDelete(); } });
            } else {
                sendDelete();
            }
            return;
        }
    }

    // add_row_action (goatcheese Parameter): a per-row <button class="gc-row-action"
    // data-gc-action data-gc-model data-gc-pk [data-gc-confirm]> emitted into the
    // action cell of the main list and of child lists. Optional confirm, then
    // POST {Model}/{action} with i=<pk json>; the Service answers a
    // {status:'success'|'error', message} envelope which is toasted, and the
    // list the button lives in is re-fetched (same fetchList path as every
    // other refresh, so gc:list-refreshed fires for derived-data listeners).
    function onRowActionClick(e) {
        var btn = e.target.closest && e.target.closest('.gc-row-action[data-gc-action]');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (btn.disabled) { return; }
        var container = btn.closest('.va-mob.proto-app[data-model]');
        var model = btn.getAttribute('data-gc-model') || (container ? container.getAttribute('data-model') : '');
        var action = btn.getAttribute('data-gc-action');
        if (!model || !action) { return; }
        var pk = btn.getAttribute('data-gc-pk') || '';
        var prompt = btn.getAttribute('data-gc-confirm') || '';
        var say = function (text, isError) {
            if (typeof sw_message === 'function') { sw_message(text, !!isError); }
            else if (window.gcScreens && window.gcScreens.toast) { window.gcScreens.toast(text); }
        };
        var run = function () {
            btn.disabled = true;
            btn.classList.add('is-busy');
            var body = new URLSearchParams();
            body.set('i', pk);
            body.set('ui', 'editDialog');
            fetch(SITE + model + '/' + action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            }).then(function (r) { return r.text(); }).then(function (txt) {
                var j = null;
                try { j = JSON.parse(txt); } catch (err) { j = null; }
                var ok = !!(j && j.status === 'success');
                var text = (j && j.message) ? j.message : (ok ? 'Done' : 'Action failed');
                say(text, !ok);
                if (container) { fetchList(container, {}); }
                else { btn.disabled = false; btn.classList.remove('is-busy'); }
            }).catch(function () {
                btn.disabled = false;
                btn.classList.remove('is-busy');
                say('Action failed', true);
            });
        };
        if (prompt && window.gcScreens && typeof window.gcScreens.confirm === 'function') {
            window.gcScreens.confirm(prompt, { confirmLabel: 'Yes', danger: false })
                .then(function (ok) { if (ok) { run(); } });
        } else {
            run();
        }
    }

    // Enter in the inline new-folder field submits it.
    function onDriveNfKey(e) {
        if (e.keyCode !== 13) { return; }
        var inp = e.target.closest && e.target.closest('[data-drive-nf-input]');
        if (!inp) { return; }
        e.preventDefault();
        var container = driveContainer(inp);
        if (!container) { return; }
        driveCreateFolder(container, container.getAttribute('data-model'), container.getAttribute('data-dpath') || '', inp.value);
    }
    function onDriveFileChosen(e) {
        var inp = e.target;
        if (!inp || !inp.matches || !inp.matches('input[type="file"][data-drive-upload]')) { return; }
        var container = driveContainer(inp);
        if (!container || !inp.files || !inp.files.length) { return; }
        var model = container.getAttribute('data-model');
        var dpath = container.getAttribute('data-dpath') || '';
        driveLoaderShow(container);
        var fd = new FormData();
        fd.append('file', inp.files[0]);
        fd.append('name', inp.files[0].name);
        fd.append('dpath', dpath);
        fetch(SITE + model + '/uploadFile', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        }).then(function (r) { return r.json().catch(function () { return {}; }); })
          .then(function (res) {
              if (res && res.ok === false && res.error) { alertb('Error', res.error); } // #no-native-alert
              inp.value = '';
              fetchList(container, { dpath: dpath });
          }).catch(function () { inp.value = ''; driveLoaderHide(); });
    }

    function init() {
        relocateSwHeaderActions();
        var containers = listContainers();
        if (!containers.length) { return; }
        // Defer so jQuery ready handlers (legacy binding) have run first;
        // then cloning actually strips their handlers.
        setTimeout(function () {
            listContainers().forEach(enhance);
        }, 0);
        // Capture phase, registered before onDtRowCapture: a thread row's
        // click must expand/collapse in place rather than fall through to
        // the push-screen edit-drawer navigation below.
        document.addEventListener('click', onThreadRowClick, true);
        // Capture phase: must beat the legacy per-cell bindEdit handler.
        document.addEventListener('click', onDtRowCapture, true);
        document.addEventListener('click', onContainerClick, false);
        document.addEventListener('click', onFilterBtn, false);
        document.addEventListener('click', onChipClick, false);
        document.addEventListener('click', onActiveFilterClick, false);
        document.addEventListener('click', onDriveNav, false);
        document.addEventListener('click', onDriveAction, false);
        document.addEventListener('click', onRowActionClick, false);
        document.addEventListener('keydown', onDriveNfKey, false);
        document.addEventListener('change', onDriveFileChosen, false);
        document.addEventListener('click', onRowCheckboxLabelClick, true);
        document.addEventListener('input', onSearchInput, false);
        document.addEventListener('keydown', onSearchKey, false);
        document.addEventListener('input', function (e) {
            var f = e.target && e.target.closest
                ? e.target.closest('[id^="formMs"]') : null;
            if (f) { syncFilterBtn(f); }
        }, false);
        document.addEventListener('keydown', onPageKey, false);
        // Full text on hover for any cell the CSS is clipping. The whole value
        // is already in the DOM — only `text-overflow: ellipsis` hides it — so
        // a title is all that is needed, and measuring lazily on hover costs
        // nothing on render and stays correct when the column is resized.
        document.addEventListener('mouseover', onCellHover, true);
        document.addEventListener('toggle', onRowActionsToggle, true);
        document.addEventListener('click', closeRowActions, true);
        window.addEventListener('scroll', closeRowActions, true);
        window.addEventListener('resize', closeRowActions, false);
    }

    // Minimal hook so the push-screen client (A6) can refresh the
    // underlying list after a save/delete without a full reload.
    window.gcList = {
        refresh: function () {
            var c = listContainers()[0];
            if (c) { fetchList(c, {}); }
        },
        // Refresh ONE list by its table name, keeping its current page/sort/
        // filter state. Used by realtime.js when the server reports that table
        // changed; routes through the same containerForTable + fetchList pair
        // as every other refresh, so there is no second fetch path.
        refreshTable: function (table) {
            var container = containerForTable(table);
            if (container) { fetchList(container, {}); }
        },
        // Region 4 seam (additive — does not change `refresh`). filter.js
        // mutates the live #formMs{T} named inputs (fan-out + chip/segment
        // selections) then calls this; serialize/fetch stays single-
        // sourced here so the `ms` contract is unforked (spec §6/§8).
        applyFilter: function (form) {
            if (!form) { return; }
            var container = containerForTable(form.id.replace(/^formMs/, ''));
            if (container) { fetchList(container, { pg: 1 }); }
            // Keep the funnel X-toggle + badge in sync after Apply.
            syncFilterBtn(form);
        }
    };

    /**
     * Give a clipped table cell a title so the full value is readable on
     * hover. Skips the action cell (buttons, not text) and anything that
     * already carries a title of its own.
     */
    function onCellHover(e) {
        var td = e.target && e.target.closest ? e.target.closest('td') : null;
        if (!td || td.hasAttribute('title') || td.classList.contains('actionrow')) { return; }
        if (td.scrollWidth > td.clientWidth) {
            var txt = (td.innerText || '').trim();
            if (txt) { td.setAttribute('title', txt); }
        }
    }

    /**
     * Place a grouped row-action menu.
     *
     * The list table scrolls (`overflow: auto hidden`), so an absolutely
     * positioned menu inside a cell is clipped the moment it extends past
     * the row — it opened as an empty white box. `position: fixed` escapes
     * the scroll container, but then the coordinates have to be computed,
     * which is what this does: right-aligned under the trigger, flipped
     * above it when there is no room below.
     */
    function placeRowActions(d) {
        var menu = d.querySelector('.gc-row-actions-menu');
        var sum = d.querySelector('summary');
        if (!menu || !sum) { return; }
        var r = sum.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.left = 'auto';
        menu.style.right = Math.round(window.innerWidth - r.right) + 'px';
        menu.style.top = Math.round(r.bottom + 4) + 'px';
        var h = menu.offsetHeight || 0;
        if (h && r.bottom + 4 + h > window.innerHeight) {
            menu.style.top = Math.max(4, Math.round(r.top - 4 - h)) + 'px';
        }
    }

    /** Open at most one menu, and place it. `toggle` does not bubble: capture. */
    function onRowActionsToggle(e) {
        var d = e.target;
        if (!d || !d.classList || !d.classList.contains('gc-row-actions')) { return; }
        if (!d.open) { return; }
        Array.prototype.forEach.call(
            document.querySelectorAll('details.gc-row-actions[open]'),
            function (o) { if (o !== d) { o.open = false; } }
        );
        placeRowActions(d);
    }

    /** Anything outside an open menu closes it — including scrolling the list. */
    function closeRowActions(e) {
        var inside = e && e.target && e.target.closest
            ? e.target.closest('details.gc-row-actions') : null;
        Array.prototype.forEach.call(
            document.querySelectorAll('details.gc-row-actions[open]'),
            function (o) { if (o !== inside) { o.open = false; } }
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();
