/*
 * realtime.js — live list refresh over the OpenSwoole sidecar.
 *
 * Flow: GET {SITE}rt/ticket for a short-lived signed ticket, open a WebSocket
 * to the sidecar through the Apache ws-tunnel, subscribe to the tables that are
 * on screen, and re-fetch a list when the server says that table changed.
 *
 * The socket carries NO row data — only {op:'change', t:<table>} — so nothing
 * here can display data the user is not allowed to see. The refresh goes back
 * through gcList, i.e. the normal authenticated endpoint, where RBAC/ACL/tenant
 * scoping applies exactly as it does on a manual reload.
 *
 * Everything degrades: no ticket endpoint, disabled project, sidecar down or
 * WebSocket unsupported all leave the page working exactly as before.
 *
 * Vanilla, no jQuery, no overlay (house rule).
 */
(function () {
    'use strict';

    if (typeof window === 'undefined' || !window.WebSocket) { return; }

    // _SITE_URL is declared `let` in an inline page script (BuilderLayout), so
    // it is not a window property in every browser — read both, like list.js.
    var SITE = (typeof _SITE_URL !== 'undefined' && _SITE_URL)
        || (typeof window !== 'undefined' && window._SITE_URL)
        || '';

    var ws = null;
    var subs = {};              // DB table -> data-table (desired subscriptions)
    var pinned = {};            // DB table -> true (kept by project listeners, not by the DOM)
    var sent = {};              // table -> true (subscriptions the socket knows)
    var backoff = 1000;         // ms, doubles to BACKOFF_MAX on repeated failure
    var BACKOFF_MAX = 30000;
    var disabled = false;       // project has GC_RT_ENABLED off — stop for good
    var closing = false;
    var pending = {};           // table -> true (changes waiting for the debounce)
    var debounceTimer = null;
    var DEBOUNCE = 400;

    function log() {
        if (window.gcRealtimeDebug && window.console) {
            console.log.apply(console, ['[gc:rt]'].concat([].slice.call(arguments)));
        }
    }

    /* ---------------------------------------------------------------- tables */

    /**
     * Tables currently on screen.
     *
     * Subscribe by the DB table name from data-gc-db, NOT data-table: the
     * latter is the model PhpName ('Product', 'ApiRbac') while the ORM bumps
     * the DB name ('product', 'api_rbac'). They differ by more than case, so
     * matching on data-table silently never fires. data-gc-db is emitted
     * alongside it by getList/childListBuilder for exactly this reason.
     *
     * The value is also mapped back to its data-table so a change frame can be
     * routed to gcList.refreshTable(), which keys on data-table.
     */
    function visibleTables() {
        var out = {};
        var nodes = document.querySelectorAll('.va-mob.proto-app[data-gc-db]');
        for (var i = 0; i < nodes.length; i++) {
            var db = nodes[i].getAttribute('data-gc-db');
            if (db) { out[db] = nodes[i].getAttribute('data-table') || db; }
        }
        return out;
    }

    function anyTables() {
        for (var k in subs) { if (Object.prototype.hasOwnProperty.call(subs, k)) { return true; } }
        return false;
    }

    /** Re-sync subscriptions to what is on screen (called on every screen change). */
    function syncSubscriptions() {
        subs = visibleTables();
        // Tables a project registered a listener for are not on screen, so
        // visibleTables() cannot see them — re-add them or every screen change
        // would silently unsubscribe them.
        for (var pt in pinned) {
            if (Object.prototype.hasOwnProperty.call(pinned, pt) && !subs[pt]) { subs[pt] = pt; }
        }

        // Nothing to watch (login screen, welcome dashboard, a form-only page):
        // do not open a socket and do not even ask for a ticket. Besides being
        // wasted work, the unauthenticated 401 left a request the page never
        // settled, so networkidle never fired and `gc verify` hung on login.
        if (!anyTables()) { return; }
        if (!ws) { connect(); return; }
        if (ws.readyState !== 1) { return; }

        var add = [], drop = [], t;
        for (t in subs) { if (!sent[t]) { add.push(t); } }
        for (t in sent) { if (!subs[t]) { drop.push(t); } }
        if (add.length) { ws.send(JSON.stringify({ op: 'sub', tables: add })); }
        if (drop.length) { ws.send(JSON.stringify({ op: 'unsub', tables: drop })); }
        if (add.length || drop.length) {
            sent = {};
            for (t in subs) { sent[t] = true; }
            log('subscriptions', Object.keys(sent));
        }
    }

    /* --------------------------------------------------------------- refresh */

    /**
     * Apply queued changes. Debounced, because one user action (a save with
     * child rows, a mass action) bumps several tables in quick succession and
     * each bump would otherwise cost a full list fetch.
     */
    function flush() {
        debounceTimer = null;
        var tables = Object.keys(pending);
        pending = {};
        if (!tables.length || !window.gcList) { return; }

        // Never yank the ground out from under someone who is mid-edit: a drawer
        // or modal open means they are looking at a form, not the list.
        if (document.querySelector('.gc-drawer.open, .gc-modal.open, dialog[open]')) {
            log('deferred (drawer/modal open)', tables);
            return;
        }

        for (var i = 0; i < tables.length; i++) {
            // pending holds DB names; gcList keys on the data-table PhpName.
            var target = subs[tables[i]] || tables[i];
            log('refresh', tables[i], '->', target);
            if (typeof window.gcList.refreshTable === 'function') {
                window.gcList.refreshTable(target);
            } else if (typeof window.gcList.refresh === 'function') {
                window.gcList.refresh();
            }
        }
    }

    function onChange(table, gen) {
        // Notify project code FIRST, and unconditionally — a project may care
        // about a table that has no list on screen (a badge count, a toast),
        // which is exactly the case the built-in refresh ignores.
        emit(table, gen);

        if (!subs[table]) { return; }
        pending[table] = true;
        if (debounceTimer) { clearTimeout(debounceTimer); }
        debounceTimer = setTimeout(flush, DEBOUNCE);
    }

    /* ------------------------------------------------------------ extension */

    // table name -> [fn]; '*' receives every change.
    var listeners = {};

    /**
     * Fan a change out to project code: registered listeners plus a DOM event.
     * A throwing listener is logged and skipped — one project bug must not stop
     * the others, nor the built-in refresh.
     */
    function emit(table, gen) {
        var detail = { table: table, gen: gen };
        var fns = (listeners[table] || []).concat(listeners['*'] || []);
        for (var i = 0; i < fns.length; i++) {
            try {
                fns[i](detail);
            } catch (e) {
                if (window.console) { console.error('[gc:rt] listener for ' + table + ' threw', e); }
            }
        }
        try {
            document.dispatchEvent(new CustomEvent('gc:realtime-change', { detail: detail }));
        } catch (e) {}
    }

    /* ------------------------------------------------------------ connection */

    function connect() {
        if (disabled || closing || (ws && ws.readyState <= 1)) { return; }

        fetch(SITE + 'rt/ticket', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) {
            // Not signed in. Two shapes: a 401 JSON envelope, or — on the login
            // page itself — a 303 to Authy/login that fetch follows, landing on
            // 200 HTML. Both are terminal: retrying against a session that does
            // not exist is a request storm that also keeps the page from ever
            // reaching networkidle (which breaks `gc verify`).
            // Every early return MUST read the body to completion. Leaving it
            // unread — or cancelling it — leaves the request open for the life
            // of the page, so the network never goes idle and Playwright's
            // networkidle wait (and therefore `gc verify`) hangs on the login
            // screen. Reading and discarding is what actually closes it.
            function drain(value) {
                return r.text().catch(function () {}).then(function () { return value; });
            }
            if (r.status === 401 || r.redirected) { disabled = true; return drain(null); }
            if (!r.ok) { return drain(false); }  // 5xx / transient — worth a retry
            var ct = r.headers.get('Content-Type') || '';
            if (ct.indexOf('application/json') === -1) { disabled = true; return drain(null); }
            // A malformed body is a server-side problem, not a transient one.
            return r.json().catch(function () { disabled = true; return null; });
        }).then(function (cfg) {
            if (cfg === null) { return; }       // terminal — stop for good
            if (cfg === false) { retry(); return; }
            if (!cfg.enabled || !cfg.ticket) {
                log('realtime disabled for this project');
                disabled = true;
                return;
            }
            open(cfg);
        }).catch(function () { retry(); });     // network-level failure only
    }

    function open(cfg) {
        var proto = location.protocol === 'https:' ? 'wss://' : 'ws://';
        var url = proto + location.host + cfg.path + '?ticket=' + encodeURIComponent(cfg.ticket);

        try {
            ws = new WebSocket(url);
        } catch (e) { retry(); return; }

        ws.onopen = function () {
            log('connected');
            backoff = 1000;
            sent = {};
            syncSubscriptions();
        };

        ws.onmessage = function (ev) {
            var msg;
            try { msg = JSON.parse(ev.data); } catch (e) { return; }
            if (msg && msg.op === 'change' && msg.t) { onChange(msg.t, msg.g); }
        };

        ws.onclose = function () { ws = null; retry(); };
        // onerror is always followed by onclose; let onclose own the retry.
        ws.onerror = function () { log('socket error'); };
    }

    function retry() {
        if (disabled || closing) { return; }
        log('reconnect in', backoff, 'ms');
        setTimeout(function () {
            // The ticket is single-use and short-lived, so every reconnect
            // starts by minting a fresh one — never by replaying the old.
            connect();
        }, backoff);
        backoff = Math.min(backoff * 2, BACKOFF_MAX);
    }

    /* --------------------------------------------------------------- wire-up */

    function init() {
        // connect() is driven by syncSubscriptions(): it fires only once there
        // is a list on screen worth watching.
        syncSubscriptions();

        // The screen client swaps list containers in place; re-read what is on
        // screen whenever it says a list changed.
        document.addEventListener('gc:list-refreshed', syncSubscriptions, false);
        document.addEventListener('gc:screen-changed', syncSubscriptions, false);

        // A tab in the background gets throttled timers; re-check on return.
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                if (!ws) { backoff = 1000; }
                syncSubscriptions();
            }
        }, false);

        window.addEventListener('beforeunload', function () {
            closing = true;
            if (ws) { try { ws.close(); } catch (e) {} }
        }, false);
    }

    window.gcRealtime = {
        connected: function () { return !!ws && ws.readyState === 1; },
        tables: function () { return Object.keys(sent); },
        sync: syncSubscriptions,

        /**
         * Run fn({table, gen}) whenever the server reports that DB table
         * changed. Use the DB table name ('invoice', 'api_rbac'), not the model
         * PhpName. '*' receives every change. Returns an unsubscribe function.
         *
         * Put your listeners in public/js/project.js — it loads last and is
         * never overwritten by a template sync.
         */
        on: function (table, fn) {
            if (typeof fn !== 'function' || !table) { return function () {}; }
            (listeners[table] = listeners[table] || []).push(fn);
            // A project may care about a table that is not on any list on
            // screen; make sure we are actually subscribed to it server-side.
            if (table !== '*' && !pinned[table]) {
                pinned[table] = true;
                subs[table] = subs[table] || table;
                if (ws && ws.readyState === 1) {
                    ws.send(JSON.stringify({ op: 'sub', tables: [table] }));
                    sent[table] = true;
                } else if (!ws) {
                    connect();
                }
            }
            return function () {
                var a = listeners[table] || [];
                var i = a.indexOf(fn);
                if (i !== -1) { a.splice(i, 1); }
            };
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();
