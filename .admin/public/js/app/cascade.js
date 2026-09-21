/* GoatCheese live selectbox cascade (ChildSelect, rebuilt vanilla).
 *
 * Declarative parent → child select cascade for the generated edit form. The
 * emitter stamps the CHILD's label.select-label with
 *
 *   data-gc-cascade='{"model":"Assignment","field":"IdAssignmentOption",
 *                     "srcs":["IdAssignmentKind"],"required":0,
 *                     "seed":{"IdAssignmentKind":"3"}}'
 *
 * and this binder re-fetches the child's option list from
 * `POST {model}/selectbox` whenever one of `srcs` changes, then hands the
 * options to gcSelectBox.setOptions(). N levels come for free: the reload
 * dispatches a bubbling 'change' on the child's hidden input ONLY when the
 * child's value actually moved, and a grandchild listening on that input
 * reloads in turn (a same-value reload stops the chain).
 *
 * Ordering matters. gcSelectBox.bindOne() auto-picks the first <li> when the
 * hidden input is empty, and pick() always fires a bubbling 'change' — so this
 * binder must run AFTER the widget is bound (screens.js push() calls us after
 * gcSelectBox.bindWithin; on a full page load the emitter's inline bind runs at
 * parse time, well before our DOMContentLoaded self-init). We snapshot each
 * source's value at bind time and ignore same-value change echoes, so a
 * re-bind or a widget re-render never triggers a spurious fetch.
 *
 * `seed` closes the hole that ordering leaves open: it is what the SERVER
 * filtered the rendered option list with, so if a source input already holds
 * something else by the time we bind (the auto-pick above, on a new record with
 * a required parent FK), the list on screen is stale and we reload once.
 *
 * Scope: the edit form only. Search (#formMs<T>) and bulk-update (#formBu<T>)
 * forms reuse the same field markup and may carry the attribute; they are
 * skipped — their selects filter/patch many rows and must keep the full list.
 */
window.gcCascade = (function () {
    'use strict';

    // _SITE_URL is declared `let` in an inline page script, so it lives in the
    // global lexical scope, NOT on window. Read it via a typeof guard (a bare
    // reference to an undeclared name would throw).
    var SITE = (typeof _SITE_URL !== 'undefined' && _SITE_URL)
        || (typeof window !== 'undefined' && window._SITE_URL)
        || '/';

    var LOADING_CLASS = 'gc-cascade-loading';

    // The widget's "Empty value" row carries data-value="_null" (html_helper
    // selectboxCustomArray), and gcSelectBox.bindOne()'s auto-pick can select it
    // without the user touching anything when the select has no li.default row.
    // The server contract is '' => unconstrained, anything else cast per column
    // type — so posting '_null' for an int FK would arrive as 0 and silently
    // filter the child list on a row that cannot exist. Normalise it to '' both
    // in the request body AND in the same-value echo test, so '' and '_null'
    // are never mistaken for two different source values.
    function srcValue(el) {
        var v = (el && el.value != null) ? String(el.value) : '';
        return v === '_null' ? '' : v;
    }

    function fireChange(el) {
        var ev;
        try { ev = new Event('change', { bubbles: true }); }
        catch (e) { ev = document.createEvent('HTMLEvents'); ev.initEvent('change', true, false); }
        el.dispatchEvent(ev);
    }

    // Wire one child label against its source inputs.
    function bindOne(label, cfg, hidden, srcs, srcInputs) {
        // Per-child request sequence: only the newest response may land, so a
        // slow reply for an abandoned parent value can never overwrite a newer
        // list (the user can change the parent faster than the round-trip).
        var seq = 0;
        // Source values as they stand right now — the baseline the change
        // handler diffs against, so the widget's bind-time auto-pick echo (and
        // any other same-value 'change') is ignored.
        var last = srcInputs.map(function (el) { return srcValue(el); });
        // Number(): `required` arrives from JSON and the string "0" is truthy.
        var mode = Number(cfg.required) === 1 ? 'keep-or-first' : 'keep-or-empty';

        function reload() {
            if (!window.gcSelectBox || typeof gcSelectBox.setOptions !== 'function') { return; }
            var mySeq = ++seq;
            var params = new URLSearchParams();
            params.append('a', 'selectbox');
            params.append('field', cfg.field);
            srcInputs.forEach(function (el, i) {
                params.append('values[' + srcs[i] + ']', srcValue(el));
            });
            label.classList.add(LOADING_CLASS);
            fetch(SITE + cfg.model + '/selectbox', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: params.toString()
            }).then(function (r) {
                return r.ok ? r.json() : null;
            // Two handlers, NOT .then().catch(): a single trailing .catch also
            // swallows anything thrown inside the success path and reports it
            // as if the network had failed.
            }).then(onOk, onErr);

            function onOk(res) {
                // A newer reload is in flight and owns the loading class: drop
                // this response untouched.
                if (mySeq !== seq) { return; }
                label.classList.remove(LOADING_CLASS);
                // Error / malformed reply ({"status":"error",…}, a non-2xx, a
                // login redirect): keep the list the user is looking at.
                if (!res || res.status !== 'ok' || !Array.isArray(res.options)) { forget(); return; }
                var before = hidden.value;
                var after;
                try {
                    after = gcSelectBox.setOptions(label, res.options, mode);
                } catch (e) {
                    // A throw in the widget (or on a malformed option row) is a
                    // bug, not a failed request: say so instead of vanishing,
                    // and keep the list the user is looking at.
                    if (window.console && console.error) { console.error('gcCascade: setOptions failed', e); }
                    return;
                }
                // setOptions never fires 'change'. Fire it here ONLY when the
                // value moved — that is what chains a grandchild, and what
                // keeps a no-op reload from looping back through the cascade.
                if (after !== before) { fireChange(hidden); }
            }

            function onErr() {
                if (mySeq === seq) { label.classList.remove(LOADING_CLASS); forget(); }
            }

            // The list still belongs to the PREVIOUS parent: forget what was
            // last loaded, or re-picking the same parent reads as a no-op echo
            // and the failed reload can never be retried.
            function forget() {
                for (var f = 0; f < last.length; f++) { last[f] = null; }
            }
        }

        srcInputs.forEach(function (el, i) {
            el.addEventListener('change', function () {
                var v = srcValue(el);
                if (v === last[i]) { return; }
                last[i] = v;
                reload();
            });
        });

        // Bind-time reconciliation — the ONLY fetch that is not a user change.
        // `seed` is what the SERVER filtered this option list with. It usually
        // matches, but not on a new record with a REQUIRED parent FK: the list
        // was rendered with a null parent (filterBy(null) => empty), and then
        // gcSelectBox.bindOne() auto-picked the parent's first <li> — a value
        // nobody will "change" again, leaving the child empty forever. An
        // emitter that sends no `seed` (older build) reconciles nothing.
        if (cfg.seed && typeof cfg.seed === 'object') {
            for (var s = 0; s < srcs.length; s++) {
                var seeded = cfg.seed[srcs[s]];
                if (last[s] !== String(seeded == null ? '' : seeded)) { reload(); break; }
            }
        }
    }

    // Bind every [data-gc-cascade] child select inside `scope`. Idempotent
    // (__gcCascBound per label), so screens.js may call it on every push.
    function bindWithin(scope) {
        scope = scope || document;
        if (!scope.querySelectorAll) { return; }
        var labels = Array.prototype.slice.call(scope.querySelectorAll('[data-gc-cascade]'));
        if (scope.getAttribute && scope.getAttribute('data-gc-cascade') != null) { labels.push(scope); }
        labels.forEach(function (label) {
            if (label.__gcCascBound) { return; }
            // A disabled select is server-rendered read-only: nothing to reload.
            if (label.hasAttribute('disabled')) { return; }
            var cfg;
            try { cfg = JSON.parse(label.getAttribute('data-gc-cascade')); } catch (e) { return; }
            if (!cfg || !cfg.model || !cfg.field || !cfg.srcs || !cfg.srcs.length) { return; }
            var form = label.closest ? label.closest('form') : null;
            // Edit forms only — search (#formMs<T>) / bulk-update (#formBu<T>)
            // selects must keep the unfiltered list.
            if (!form || /^form(Ms|Bu)/.test(form.id || '')) { return; }
            var hidden = label.querySelector('input.selextbox-input') || label.querySelector('input');
            if (!hidden) { return; }
            var srcs = cfg.srcs;
            var srcInputs = [];
            for (var i = 0; i < srcs.length; i++) {
                var el = form.querySelector("[id='" + srcs[i] + "']");
                // A source column that is readonly/hidden on this form has no
                // input to watch. Leave the server-rendered list alone rather
                // than reloading it from a value we cannot see change.
                if (!el) { return; }
                srcInputs.push(el);
            }
            label.__gcCascBound = 1;
            bindOne(label, cfg, hidden, srcs, srcInputs);
        });
    }

    // Self-init for a form rendered by a full page load; pushed fragments are
    // covered by screens.js push() → gcCascade.bindWithin(screen).
    (function () {
        function go() { try { bindWithin(document); } catch (e) {} }
        if (document.readyState != 'loading') { go(); }
        else { document.addEventListener('DOMContentLoaded', go); }
    })();

    return { bindWithin: bindWithin };
})();
