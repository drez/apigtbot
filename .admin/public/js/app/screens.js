/* GoatCheese vanilla push-screen client (A6). No jQuery.
 *
 * Row tap (from list.js) → gcScreens.openEdit(model, pk):
 *   GET {SITE}{model}/edit/{pk}?ui=protoEditScreen  (server contract §1,
 *   ui set ⇒ HTML fragment) → slide a .proto-screen in.
 * Back (.form-nav .nav-btn) → pop the screen instead of navigating.
 * Save (#save{Model}/.nav-save) → POST {SITE}{model}/update with the
 *   serialized s='d' fields (server contract §2) → toast, pop, refresh
 *   the underlying list.
 *
 * SCSS for .proto-screen lives in _formv2 (.enter/.exit = translateX
 * 100%, default = 0). The injected fragment's legacy <script> never
 * runs (innerHTML), so legacy jQuery bindSave can't double-bind — we
 * own save here.
 */
(function () {
    'use strict';

    var SITE = (typeof _SITE_URL !== 'undefined' && _SITE_URL)
        || (typeof window !== 'undefined' && window._SITE_URL)
        || '/';
    var EDIT_UI = 'protoEditScreen';
    var stack = [];
    var sheets = [];
    // In-flight openEdit() keys (model\0pk\0ctxIp). A double-tap fires
    // openEdit twice before the first fetch resolves, so neither call sees the
    // other's screen in `stack` yet — this guards that window so the second
    // request is dropped rather than stacking a duplicate drawer.
    var pending = {};
    // Same window for openChildList(): re-selecting a child tab must reveal the
    // already-open list, not stack another copy (parent\0child\0pid keys).
    var childListPending = {};

    // --- Edit-URL / history sync -------------------------------------
    // Only the base (outermost) edit screen mirrors to the address bar;
    // nested child screens never touch history (spec §Scope). An open
    // base drawer's history entry carries state {gcEdit:{model,pk}} at
    // URL {SITE}{model}/edit/{pk}; the list entry has no gcEdit.
    function editUrl(model, pk) {
        return SITE + model + '/edit/' +
            encodeURIComponent(pk == null ? '' : pk);
    }
    function baseScreen() { return stack[0] || null; }
    function screenMatches(screen, model, pk) {
        return !!screen &&
            screen.getAttribute('data-model') === String(model) &&
            screen.getAttribute('data-pk') === String(pk == null ? '' : pk);
    }
    function stackDirty() {
        for (var i = 0; i < stack.length; i++) {
            if (stack[i].getAttribute('data-dirty') === '1') { return true; }
        }
        return false;
    }

    // Find an already-open edit drawer for this exact entity. Identity is
    // model + pk + parent context (data-ctx-ip), so an existing-record edit
    // (ctx '') and a same-pk new-child edit (ctx set) never collide. Returns
    // { screen, index } or null.
    function findOpenEdit(model, pk, ctxIp, ctxRo) {
        var mp = String(model);
        var pp = String(pk == null ? '' : pk);
        var cp = String(ctxIp || '');
        var rp = ctxRo ? '1' : '';
        for (var i = 0; i < stack.length; i++) {
            var s = stack[i];
            if (s.getAttribute('data-model') === mp &&
                s.getAttribute('data-pk') === pp &&
                (s.getAttribute('data-ctx-ip') || '') === cp &&
                (s.getAttribute('data-ctx-ro') || '') === rp) {
                return { screen: s, index: i };
            }
        }
        return null;
    }

    // Child-list counterpart of findOpenEdit: locate an already-pushed child
    // list for (parent, child, parentPk) so re-selecting its tab reveals it
    // instead of stacking a duplicate. Returns { screen, index } or null.
    function findOpenChildList(key) {
        for (var i = 0; i < stack.length; i++) {
            if (stack[i].getAttribute('data-childlist-key') === key) {
                return { screen: stack[i], index: i };
            }
        }
        return null;
    }

    // Re-assert an already-open drawer the user tried to re-open: a brief
    // scale pulse so the existing drawer is visibly the one in focus, plus
    // keyboard focus moved into its first usable control.
    function focusScreen(screen) {
        if (!screen) { return; }
        try {
            screen.animate(
                [{ transform: 'scale(1)' },
                 { transform: 'scale(0.985)' },
                 { transform: 'scale(1)' }],
                { duration: 200, easing: 'ease-out' });
        } catch (e) { /* Web Animations API absent — skip the nudge */ }
        try {
            var f = screen.querySelector(
                'input:not([type=hidden]):not([disabled]),' +
                'select:not([disabled]),textarea:not([disabled]),' +
                '.js-select-label,button:not([disabled]),[tabindex]');
            if (f && typeof f.focus === 'function') { f.focus(); }
            else { screen.setAttribute('tabindex', '-1'); screen.focus(); }
        } catch (e) { /* nothing focusable */ }
    }

    // Guideline .proto-toast (ink pill + mint check, styled in
    // _formv2.scss). No inline hex; toggle .show for the transition.
    function toast(msg) {
        var t = document.createElement('div');
        t.className = 'proto-toast';
        var ic = document.createElement('i');
        ic.className = 'ri-check-line';
        var sp = document.createElement('span');
        sp.textContent = msg;
        t.appendChild(ic);
        t.appendChild(sp);
        document.body.appendChild(t);
        requestAnimationFrame(function () { t.classList.add('show'); });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { if (t.parentNode) { t.parentNode.removeChild(t); } }, 280);
        }, 1700);
    }

    // Tolerantly parse alertb('title', 'msg') out of a legacy onReadyJs body.
    // The emitted strings are addslashes-escaped, so an apostrophe in a fr/it
    // message arrives as \' inside the JS literal — a naive [^'"]* capture
    // truncates at the backslash. Match quote-aware (allowing \-escapes), then
    // unescape for display. Returns {title, msg} or null. msg has <br> collapsed.
    function parseAlertb(text) {
        var m = String(text || '').match(
            /alertb\s*\(\s*(['"])((?:\\.|(?!\1).)*?)\1\s*,\s*(['"])((?:\\.|(?!\3).)*?)\3/);
        if (!m) { return null; }
        var unesc = function (s) { return s.replace(/\\([\s\S])/g, '$1'); };
        return {
            title: unesc(m[2]).trim(),
            msg: unesc(m[4]).replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim()
        };
    }

    // S4 — decode a JSON response envelope {status, messages[], fields[], ...}.
    // Returns the envelope or null. Legacy <script> bodies start with markup and
    // are not JSON (JSON.parse throws → null → caller falls back to the legacy
    // scrape). Only an object with a string `status` counts, so a coincidental
    // JSON body without one still falls through to legacy.
    function parseEnvelope(text) {
        var s = String(text || '');
        if (s.charAt(0) !== '{') { return null; }
        try {
            var obj = JSON.parse(s);
            return (obj && typeof obj.status === 'string') ? obj : null;
        } catch (e) { return null; }
    }

    // Designed confirm (no native confirm()/alert() — memory rule).
    function confirmDialog(message, opts) {
        opts = opts || {};
        var confirmLabel = opts.confirmLabel || 'Delete';
        var danger = opts.danger !== false; // default red
        return new Promise(function (resolve) {
            var dim = document.createElement('div');
            dim.className = 'gc-confirm-dim';
            var box = document.createElement('div');
            box.className = 'gc-confirm-box';
            var msg = document.createElement('div');
            msg.className = 'gc-confirm-msg';
            msg.textContent = message;
            var btns = document.createElement('div');
            btns.className = 'gc-confirm-btns';
            var cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.className = 'gc-confirm-btn';
            cancel.textContent = 'Cancel';
            var ok = document.createElement('button');
            ok.type = 'button';
            ok.className = 'gc-confirm-btn ' + (danger ? 'danger' : 'primary');
            ok.textContent = confirmLabel;
            btns.appendChild(cancel);
            btns.appendChild(ok);
            box.appendChild(msg);
            box.appendChild(btns);
            dim.appendChild(box);
            document.body.appendChild(dim);
            requestAnimationFrame(function () { dim.classList.add('show'); });
            // Accessibility: modal role, focus trap, Escape-to-cancel, focus
            // restore. Focus the non-destructive Cancel button by default.
            var teardownA11y = (typeof window.gcDialogA11y === 'function')
                ? window.gcDialogA11y(dim, box, {
                    role: 'alertdialog', msgEl: msg, initialFocus: cancel,
                    onCancel: function () { done(false); }
                })
                : function () {};
            var done = function (val) {
                teardownA11y();
                dim.classList.remove('show');
                setTimeout(function () {
                    if (dim.parentNode) { dim.parentNode.removeChild(dim); }
                }, 180);
                resolve(val);
            };
            cancel.addEventListener('click', function () { done(false); });
            ok.addEventListener('click', function () { done(true); });
            dim.addEventListener('click', function (e) { if (e.target === dim) { done(false); } });
        });
    }

    // Designed alert — single OK button on the gc-confirm shell. Replaces
    // the jQuery-UI #alertDialog behind alertb(). msg is either a string
    // (rendered as text — never HTML) or a caller-built DOM Node (appended,
    // e.g. upload error lists / progress bar); onClose fires after dismissal.
    function alertDialog(title, msg, onClose) {
        return new Promise(function (resolve) {
            var dim = document.createElement('div');
            dim.className = 'gc-confirm-dim';
            var box = document.createElement('div');
            box.className = 'gc-confirm-box alert';
            var hd = null;
            if (title) {
                hd = document.createElement('div');
                hd.className = 'gc-confirm-title';
                // SECURITY (review C-XSS): title/msg carry server refusal text that
                // can quote a stored record value; render as text, not HTML, so a
                // value like <img onerror> can't run. Matches toast/confirmDialog.
                hd.textContent = String(title);
                box.appendChild(hd);
            }
            var m = document.createElement('div');
            m.className = 'gc-confirm-msg';
            // SECURITY (review C-XSS): string msgs carry server text that can
            // quote a stored record value — render as text, never HTML. Trusted
            // client-built markup must be passed as a Node.
            if (msg && msg.nodeType === 1) { m.appendChild(msg); }
            else { m.textContent = msg == null ? '' : String(msg); }
            var btns = document.createElement('div');
            btns.className = 'gc-confirm-btns';
            var ok = document.createElement('button');
            ok.type = 'button';
            ok.className = 'gc-confirm-btn primary';
            ok.textContent = 'OK';
            btns.appendChild(ok);
            box.appendChild(m);
            box.appendChild(btns);
            dim.appendChild(box);
            document.body.appendChild(dim);
            requestAnimationFrame(function () { dim.classList.add('show'); });
            // Accessibility: modal role, focus trap, Escape-to-dismiss, focus
            // restore. Focus the OK button (the only action).
            var teardownA11y = (typeof window.gcDialogA11y === 'function')
                ? window.gcDialogA11y(dim, box, {
                    role: 'alertdialog', titleEl: hd, msgEl: m, initialFocus: ok,
                    onCancel: function () { done(); }
                })
                : function () {};
            var done = function () {
                teardownA11y();
                dim.classList.remove('show');
                setTimeout(function () {
                    if (dim.parentNode) { dim.parentNode.removeChild(dim); }
                }, 180);
                if (typeof onClose === 'function') { onClose(); }
                resolve();
            };
            ok.addEventListener('click', done);
            dim.addEventListener('click', function (e) { if (e.target === dim) { done(); } });
        });
    }

    function pkFromRow(row) {
        var rid = row ? row.getAttribute('rid') : null;
        if (rid == null) { return ''; }
        try {
            var parsed = JSON.parse(rid);
            if (typeof parsed === 'string' || typeof parsed === 'number') {
                return String(parsed);
            }
        } catch (err) { /* non-JSON: use raw */ }
        return rid;
    }

    function doDelete(model, pk, ui, container) {
        fetchText(SITE + model + '/delete/' + encodeURIComponent(pk), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'X-GC-Envelope': '1'
            },
            body: 'ui=' + encodeURIComponent(ui || '')
        }).then(function (res) {
            if (!res) { return; }
            // The /delete endpoint returns HTTP 200 even when the server
            // REFUSES the delete (record in use): the body is a legacy
            // onReadyJs <script> calling alertb('<Type>','<reason>') and
            // carries no sw_message success marker. We don't eval the legacy
            // jQuery, so that alert never fires — surface its message in our
            // vanilla dialog and skip the success toast/refresh, since nothing
            // was actually deleted. (Mirrors applyValidationErrors for /update.)
            var text = res.text || '';
            var onDeleted = function () {
                toast('Deleted');
                if (container && container.getAttribute('data-ip')) {
                    refreshChildWrapper(container);
                } else if (window.gcList &&
                    typeof window.gcList.refresh === 'function') {
                    window.gcList.refresh();
                }
            };
            // S4 — structured envelope (sent X-GC-Envelope): decode the outcome.
            var env = parseEnvelope(text);
            if (env) {
                if (env.status === 'ok') {
                    onDeleted();
                } else {
                    var m0 = (env.messages && env.messages[0]) || {};
                    alertDialog(m0.title || 'Cannot delete', m0.text || 'This entry cannot be deleted.');
                }
                return;
            }
            // Legacy <script>-body fallback: the /delete endpoint returns HTTP
            // 200 even when the server REFUSES (record in use) — the body is an
            // onReadyJs alertb('<Type>','<reason>') with no sw_message marker.
            if (/alertb\s*\(/.test(text) && !/sw_message\s*\(/.test(text)) {
                var a = parseAlertb(text);
                alertDialog(
                    (a && a.title) || 'Cannot delete',
                    (a && a.msg) || 'This entry cannot be deleted.'
                );
                return;
            }
            if (res.status >= 200 && res.status < 300) {
                onDeleted();
            } else {
                toast('Delete failed');
            }
        }).catch(function () { toast('Delete failed'); });
    }

    // --- breadcrumbs (parent → child trail in the drawer form-nav) ----
    // Best human title for a stacked screen: the record name, else the entity
    // type, else the plain nav-title text (child-list drawers).
    function screenTitle(s) {
        if (!s) { return ''; }
        var n = s.querySelector('.form-nav .nav-title-name');
        if (n && n.textContent.trim()) { return n.textContent.trim(); }
        var t = s.querySelector('.form-nav .nav-title-type');
        if (t && t.textContent.trim()) { return t.textContent.trim(); }
        var g = s.querySelector('.form-nav .nav-title');
        if (!g) { return ''; }
        // Plain-title drawers (child lists): read the title text but EXCLUDE the
        // breadcrumb trail we may have injected into this same .nav-title, or it
        // would recurse ("Project › Time" instead of "Time").
        var txt = '';
        Array.prototype.forEach.call(g.childNodes, function (node) {
            if (node.nodeType === 1 && node.classList &&
                node.classList.contains('nav-crumbs')) { return; }
            txt += node.textContent || '';
        });
        return txt.trim();
    }

    // Prepend a clickable parent→…→current trail into the screen's nav-title.
    // `depth` is the screen's index in the stack; ancestors are stack[0..depth-1].
    // Each crumb pops back to that ancestor. Built once at push (a screen's
    // ancestors never change — you only ever pop from the top).
    function buildBreadcrumbs(screen, depth) {
        if (!screen || depth < 1) { return; }
        var navTitle = screen.querySelector('.form-nav .nav-title');
        if (!navTitle) { return; }
        var old = navTitle.querySelector('.nav-crumbs');
        if (old) { old.parentNode.removeChild(old); }
        var curType = '';
        var ct = screen.querySelector('.form-nav .nav-title-type');
        if (ct) { curType = ct.textContent.trim(); }
        var crumbs = document.createElement('span');
        crumbs.className = 'nav-crumbs';
        var prev = '';
        for (var i = 0; i < depth; i++) {
            var title = screenTitle(stack[i]);
            // Skip blanks, consecutive duplicates, and an ancestor whose title
            // equals the current entity type (e.g. the intermediate child-LIST
            // drawer "Order line" right before the "Order line" edit form).
            if (!title || title === prev || title === curType) { continue; }
            prev = title;
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'nav-crumb';
            b.setAttribute('data-crumb-depth', String(i));
            b.title = title;
            b.textContent = title;
            crumbs.appendChild(b);
            var sep = document.createElement('span');
            sep.className = 'nav-crumb-sep';
            sep.textContent = '›';
            crumbs.appendChild(sep);
        }
        if (crumbs.childNodes.length) {
            navTitle.insertBefore(crumbs, navTitle.firstChild);
        }
    }

    // Pop screens until the stack is `targetLen` deep (never pops the base, so
    // targetLen >= 1). One discard prompt if any popped screen is dirty.
    function popTo(targetLen) {
        if (targetLen < 1 || stack.length <= targetLen) { return; }
        var dirty = false;
        for (var i = targetLen; i < stack.length; i++) {
            if (stack[i].getAttribute('data-dirty') === '1') { dirty = true; break; }
        }
        var run = function () {
            for (var j = targetLen; j < stack.length; j++) {
                stack[j].setAttribute('data-dirty', '0');
            }
            while (stack.length > targetLen) { pop(false); }
        };
        if (dirty) {
            confirmDialog('Discard unsaved changes?', { confirmLabel: 'Discard', danger: true })
                .then(function (ok) { if (ok) { run(); } });
        } else {
            run();
        }
    }

    // ACL rights-matrix binder (Authy user form). Mirrors the emitter's
    // addRightsOnReadyJs (AuthyRights.php) exactly — same per-element guard
    // flags so it is idempotent with the re-exec'd inline block during the S5
    // transition. Controls are scanned within the pushed screen; the cross-row
    // toggle targets ([j=rcRights*], [ent=*]) stay document-wide as the
    // original did, and the Group/container sync keeps its #formAuthy fallback.
    function bindRightsMatrix(scope) {
        if (!scope || !scope.querySelectorAll) { return; }
        Array.prototype.forEach.call(scope.querySelectorAll('[j=mass-action-Rights]'), function (ma) {
            if (ma.__maBound) { return; } ma.__maBound = 1;
            ma.addEventListener('click', function () {
                var target = this.getAttribute('target');
                var ck = this.checked;
                Array.prototype.forEach.call(document.querySelectorAll("[j='rcRights" + target + "']"), function (c) { c.checked = ck; });
            });
        });
        Array.prototype.forEach.call(scope.querySelectorAll("[j='chkRights']"), function (ch) {
            if (ch.__chkBound) { return; } ch.__chkBound = 1;
            ch.addEventListener('click', function () {
                var ents = document.querySelectorAll("[ent='" + this.getAttribute('i') + "']");
                var newState = ents[0] ? !ents[0].checked : true;
                Array.prototype.forEach.call(ents, function (e) { e.checked = newState; });
            });
        });
        var grp = scope.querySelector('#Group') || document.querySelector('#formAuthy #Group');
        var ctnr = scope.querySelector('#genRightsCtnr') || document.querySelector('#formAuthy #genRightsCtnr');
        if (grp || ctnr) {
            var syncRights = function () { if (!ctnr) { return; } ctnr.style.display = (grp && grp.value == 'Admin') ? 'none' : ''; };
            syncRights();
            if (grp && !grp.__grpBound) { grp.__grpBound = 1; grp.addEventListener('change', syncRights); }
        }
    }

    // Tab-panel binder for the legacy runtime Tabs widget (Html/Tabs.php), used
    // by the Admin rights matrix (All/Group/Owner sub-tabs). Anchors render
    // j='conglet_StdTabs' t='#<panel>'; selecting one shows its target panel and
    // hides the siblings in the target's container, toggling .selected/.ui-state-active
    // on the <li>. Ports Tabs.php getOnReadyJs (initial default + click toggle) off
    // the push() <script> re-exec arm (#23 S5). Idempotent (__stdTabsBound). Coexists
    // with the conglet child-tab handler, which falls through for StdTabs (no
    // Id<Parent> input). No-op on screens without these tabs.
    function showStdTab(tab) {
        var sel = tab && tab.getAttribute('t');
        var target = sel && document.querySelector(sel);
        if (!target || !target.parentNode) { return; }
        var cont = target.parentNode;
        Array.prototype.forEach.call(cont.children, function (d) {
            if (d.tagName === 'DIV') { d.style.display = 'none'; }
        });
        Array.prototype.forEach.call(document.querySelectorAll("[j='conglet_StdTabs'][t]"), function (t) {
            var g = document.querySelector(t.getAttribute('t'));
            if (g && g.parentNode === cont && t.parentNode) {
                t.parentNode.classList.remove('selected', 'ui-state-active');
            }
        });
        if (tab.parentNode) { tab.parentNode.classList.add('selected', 'ui-state-active'); }
        target.style.display = '';
    }
    function bindStdTabs(scope) {
        if (!scope || !scope.querySelectorAll) { return; }
        var tabs = scope.querySelectorAll("[j='conglet_StdTabs'][t]");
        if (!tabs.length) { return; }
        var conts = [];
        Array.prototype.forEach.call(tabs, function (tab) {
            if (!tab.__stdTabsBound) {
                tab.__stdTabsBound = 1;
                tab.addEventListener('click', function (e) {
                    if (e && e.preventDefault) { e.preventDefault(); }
                    showStdTab(tab);
                });
            }
            var tg = document.querySelector(tab.getAttribute('t'));
            if (tg && tg.parentNode && conts.indexOf(tg.parentNode) === -1) { conts.push(tg.parentNode); }
        });
        // Initial selection per container: the tab the markup marks default
        // (li.selected) or, failing that, the first tab.
        conts.forEach(function (cont) {
            var groupTabs = Array.prototype.filter.call(tabs, function (t) {
                var g = document.querySelector(t.getAttribute('t'));
                return g && g.parentNode === cont;
            });
            if (!groupTabs.length) { return; }
            var def = null;
            groupTabs.forEach(function (t) {
                if (t.parentNode && t.parentNode.classList.contains('selected')) { def = t; }
            });
            showStdTab(def || groupTabs[0]);
        });
    }

    function push(html, model, pk, syncHistory) {
        var screen = document.createElement('div');
        screen.className = 'proto-screen enter';
        screen.style.zIndex = String(30 + stack.length);
        screen.setAttribute('data-model', model);
        screen.setAttribute('data-pk', pk == null ? '' : String(pk));
        screen.innerHTML = html;
        document.body.appendChild(screen);
        // Force reflow so the .enter→(removed) transition animates.
        // eslint-disable-next-line no-unused-expressions
        screen.offsetWidth;
        screen.classList.remove('enter');
        stack.push(screen);
        // Parent → child breadcrumb trail in this screen's form-nav.
        buildBreadcrumbs(screen, stack.length - 1);
        document.body.classList.add('screen-open');
        // History sync: the base screen adds an edit history entry.
        // popstate-driven restores pass syncHistory=false (the URL is
        // already correct) so they do not double-push.
        if (syncHistory && stack.length === 1) {
            history.pushState(
                { gcEdit: { model: String(model),
                            pk: String(pk == null ? '' : pk) } },
                '', editUrl(model, pk));
        }
        // Unsaved-changes tracking: the screen is "dirty" only when its
        // payload ACTUALLY differs from what was loaded. We snapshot the
        // payload once initialisation finishes (screen._gcBaseline, captured
        // at the end of push()) and compare against it on every edit, rather
        // than flipping a flag on the first event seen. That distinction
        // matters because re-picking the option already selected — and the
        // mobile sheet picker (choose()) — both fire a 'change' even when the
        // value is unchanged; a first-event flag would mark the form dirty and
        // prompt "Discard unsaved changes?" on close over an edit that never
        // happened. Editing a field and putting it back to its original value
        // likewise nets to clean.
        // NtN crossref junction rows + bulk-select checkboxes carry
        // j="check_multi_…" and are NOT part of the [s='d'] payload: the
        // junction auto-saves on change (childListBuilder NtNsave) and the
        // bulk selectors only drive mass actions, so toggling them is never an
        // unsaved edit. Same discriminator list.js uses for these controls.
        screen.setAttribute('data-dirty', '0');
        var markDirty = function (e) {
            var t = e && e.target;
            if (t && t.getAttribute &&
                /^check_multi_/.test(t.getAttribute('j') || '')) { return; }
            // Ignore events fired while the form is still initialising
            // (selectbox bind, inline onReadyJs setVal): those values belong
            // to the baseline, which is captured at the end of push().
            if (screen._gcBaseline == null) { return; }
            // serializeData() is the exact POST payload save() sends, so a
            // diff against the baseline means the saved record would change.
            var dirty = serializeData(screen) !== screen._gcBaseline;
            screen.setAttribute('data-dirty', dirty ? '1' : '0');
            // Port of the legacy $.fn.bindFormKeypress field handler: clear the
            // validation-error styling on the field being edited and flag the
            // save button unsaved (main.scss .can-save.unsaved green highlight).
            // The legacy formChanged{Model} hidden field is intentionally not
            // set — nothing reads it server-side.
            if (t && t.classList) {
                t.classList.remove('error_field');
                var lblWrap = t.closest ? t.closest('.js-select-label') : null;
                if (lblWrap) {
                    var lblSpan = lblWrap.querySelector('.select-label-span');
                    if (lblSpan) { lblSpan.classList.remove('error_field'); }
                }
            }
            // Reflect the net state: reverting every edit clears the cue too.
            var saveBtn = screen.querySelector(".nav-save, [id^='save']");
            if (saveBtn) { saveBtn.classList.toggle('unsaved', dirty); }
        };
        screen.addEventListener('input', markDirty, true);
        screen.addEventListener('change', markDirty, true);
        // Bind drawer sub-controllers (tab switching etc.) on the freshly
        // injected screen. GcDrawer.init is idempotent (__gcBound guard)
        // so safe to call here and again from T10 AJAX child rebind.
        if (window.GcDrawer && typeof window.GcDrawer.init === 'function') {
            window.GcDrawer.init(screen);
        }
        // #23 S5: hide the page loader on push — moved here off the per-form
        // __gcReady block (Form.php) as that block empties toward arm deletion.
        var __ld = document.getElementById('loader');
        if (__ld && getComputedStyle(__ld).display === 'block') { __ld.style.display = 'none'; }
        // Bind the vanilla selectbox widget on every .js-select-label inside
        // the freshly-injected screen. The inline init scripts the server
        // emitted with the form ran in the *fragment* string but innerHTML
        // doesn't execute <script>, so the widget would never catch clicks.
        // bindWithin() guards per-label on _gcBound so calling here is
        // idempotent.
        try {
            if (window.gcSelectBox) {
                gcSelectBox.bindWithin(screen);
            }
        } catch (e) { /* selectbox.js absent; nothing to bind */ }
        // Live parent -> child select cascade (data-gc-cascade on the child
        // label). MUST run AFTER gcSelectBox.bindWithin above: the widget's
        // bind-time auto-pick fires a 'change' on the hidden input, and the
        // cascade binder snapshots its source values when it binds -- binding
        // it second means that echo lands before any cascade listener exists.
        try {
            if (window.gcCascade) { gcCascade.bindWithin(screen); }
        } catch (e) { /* cascade.js absent; nothing to bind */ }
        try {
            if (window.gcColorField) { gcColorField.bindWithin(screen); }
        } catch (e) { /* colorfield.js absent; nothing to bind */ }
        // Bind the vanilla tag-input (multiple_fenetre chip) widget — same
        // declarative pattern as selectbox/colorfield above. Its config lives
        // entirely in DOM attributes (.gc-taginput + data-tag-search +
        // data-tag-options), so a DOM scan fully initializes it without the
        // re-executed inline `gcTagInput.bindWithin(form…)` block the emitter
        // ships. bindWithin guards each box on data-gc-tag, so this is
        // idempotent and safe to run alongside that inline block (S5: moving
        // widget init off the <script> re-exec arm, one widget at a time).
        try {
            if (window.gcTagInput) { gcTagInput.bindWithin(screen); }
        } catch (e) { /* taginput.js absent; nothing to bind */ }
        // Bind CKEditor on every WYSIWYG textarea — same reason as the
        // SelectBox binding above: the inline editor-init the generator emits
        // inside the form fragment never runs because innerHTML doesn't execute
        // its <script>. gcEditor owns the CKEditor 5 lifecycle and the
        // .tinymce + name-pattern selection (it matches Body/Note/Description/
        // Content for I18n columns the InterfaceBuilder doesn't stamp .tinymce
        // on). bindWithin is idempotent and creates editors asynchronously.
        try {
            if (window.gcEditor) { gcEditor.bindWithin(screen); }
        } catch (e) { /* CKEditor absent or bind failure; degrade gracefully */ }
        // Bind the ACL rights matrix (Authy user form). The emitted onReadyJs
        // (AuthyRights.php) already guards each control on __maBound/__chkBound/
        // __grpBound, so this client bind reuses those same flags and coexists
        // with the re-exec'd block without double-binding (S5: another widget
        // off the <script> re-exec arm). No-op on forms without the matrix.
        try {
            bindRightsMatrix(screen);
        } catch (e) { /* no rights matrix in this screen */ }
        // Rights matrix All/Group/Owner sub-tabs (legacy Tabs widget) — off the
        // re-exec arm (#23 S5). No-op on screens without [j='conglet_StdTabs'] tabs.
        try {
            bindStdTabs(screen);
        } catch (e) { /* no std-tabs in this screen */ }
        // FK autocomplete (#23 S5/S6): bind [data-gc-autoc] inputs + depends_on
        // cascade off the form, replacing the inline wrap_autoc() onReadyJs.
        // No-op on screens without autocomplete fields.
        try {
            if (window.gcAutocomplete && gcAutocomplete.bindWithin) { gcAutocomplete.bindWithin(screen); }
        } catch (e) { /* no autocomplete in this screen */ }
        // #23 S5: highlight filled search inputs on a pushed child-list (was the
        // inline searchReadyJsFirst colorField). Child lists are pushed, not
        // enhanced by list.js, so apply it here — off the re-exec arm. Only
        // matches #formMs<Child> search forms (edit forms have no #formMs*).
        try {
            if (window.colorField) {
                screen.querySelectorAll("[id^='formMs'] input").forEach(function (i) {
                    if (i.value !== '' && i.id) { window.colorField(i.id, 'hsl(196, 100%, 92%)'); }
                });
            }
        } catch (e) { /* no search inputs to highlight */ }
        // Format date cells in the pushed form/list (data-gc-fmt-date container
        // attr). Idempotent, coexists with the emitter's formatter onReadyJs.
        try {
            if (window.gcFormat) { gcFormat.bindWithin(screen); }
        } catch (e) { /* no formatter container */ }
        // Bind the file-upload widget off its declarative config element
        // (data-gc-upl, #23 S5). Capability-keyed: present only on regenerated
        // upload tables; un-regenerated ones carry the inline onReadyJs (re-exec'd
        // below) and no config element, so this no-ops on them — no double-bind.
        try {
            if (window.gcUpload) { gcUpload.bindWithin(screen); }
        } catch (e) { /* no upload config in this screen */ }
        // Bind quick-add (#23 S5): inject the "+ Add New" link + modal off the
        // form's declarative data-gc-quickadd config. Idempotent (gcQuickAddInit
        // guards each select on data-gc-qa-bound); un-regenerated forms have no
        // config attr, so this no-ops and their inline onReadyJs handles it.
        try {
            if (window.gcQuickAdd) { gcQuickAdd.bindWithin(screen); }
        } catch (e) { /* no quick-add config in this screen */ }
        // Bind child-list bulk actions (#23 S5): mass-action toggle, shift-select,
        // selected-count and NtN save-on-check off the child list form's declarative
        // data-gc-cbulk config. Idempotent (guards each control on __maChildBound /
        // __shiftChildBound / __ntnBound); un-regenerated child lists carry the inline
        // onReadyJs (re-exec'd below) and no config attr, so this no-ops on them.
        try {
            if (window.gcChildBulk) { gcChildBulk.bindWithin(screen); }
        } catch (e) { /* no child-bulk config in this screen */ }
        // Bind list-level bulk (#23 S5): mass-action toggle / shift-select / bulk-open
        // off the list form's data-gc-lbulk config. Document-wide (controls relocate to
        // the topbar), idempotent, no-ops when absent. Covers a list opened as a screen.
        try {
            if (window.gcListBulk) { gcListBulk.bindWithin(); }
        } catch (e) { /* no list-bulk config in this screen */ }
        // Bind the bulk-edit panel save (#23 S5): the bulkUpdateForm panel opened by a
        // list/child bulk-open carries data-gc-busave; gcBulkSave wires its save handler,
        // [s=d] change->ck marking and popup-hide. Idempotent, no-ops on other screens.
        try {
            if (window.gcBulkSave) { gcBulkSave.bindWithin(screen); }
        } catch (e) { /* no bulk-save panel in this screen */ }
        // Re-execute all inline <script> tags the server emitted with
        // the form. innerHTML doesn't execute <script> by spec, so any
        // onReadyJs blocks (BulkUpdate's bind('click.saveBu'), gcUpload
        // init, CKEditor setup, etc.) are inert after injection.
        // We replace each inert <script> with a freshly-created one —
        // appending a new element causes the browser to execute it.
        // Skip rules:
        //   1. External scripts (src attribute) — already loaded at page
        //      boot; re-appending causes a second network fetch + double-exec.
        //   2. Page-loader / full-page-bootstrap scripts: the BulkUpdate
        //      endpoint returns a full HTML page whose body includes a
        //      pageLoader hide-on-load block. Re-executing it inside the
        //      panel would register a window "load" listener that makes the
        //      panel's pageLoader overlay re-appear and block interaction.
        //      Guard: skip any script whose content references "pageLoader"
        //      or "serviceWorker" (both are page-level boot concerns that
        //      must not run a second time inside a pushed panel).
        try {
            Array.prototype.forEach.call(
                screen.querySelectorAll('script'),
                function (s) {
                    if (s.src) { return; }  // skip external scripts
                    var src = s.textContent || '';
                    // skip page-level bootstrap blocks
                    if (src.indexOf('pageLoader') >= 0 ||
                            src.indexOf('serviceWorker') >= 0) { return; }
                    var n = document.createElement('script');
                    for (var ai = 0; ai < s.attributes.length; ai++) {
                        var a = s.attributes[ai];
                        n.setAttribute(a.name, a.value);
                    }
                    n.textContent = src;
                    s.parentNode.replaceChild(n, s);
                }
            );
        } catch (e) { /* script eval failure; degrade gracefully */ }
        // Baseline for net-change dirty tracking (see markDirty). Snapshot the
        // payload now that selectbox/CKEditor binding and the inline onReadyJs
        // scripts above have run, so any value they set is the clean starting
        // state rather than an unsaved edit.
        screen._gcBaseline = serializeData(screen);
        return screen;
    }

    function pop(refresh) {
        var screen = stack.pop();
        if (!screen) { return; }
        // T10 child-tabs: a tab is "active" only while its child list/screen
        // is stacked above the form. We're popping back, so clear the active
        // state on the screen we return to (drawer.js sets it on click but
        // nothing cleared it on close). No-op when the screen has no child
        // tabs (e.g. returning to a child list, not the form).
        var backScreen = stack[stack.length - 1];
        if (backScreen) {
            backScreen.querySelectorAll('.child-tab.is-active').forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
        }
        // If this is a panel with refreshOnClose, promote the flag so
        // the remove callback triggers the underlying list refresh.
        var panelRefresh = screen.getAttribute('data-panel-refresh') === '1';
        // Destroy any CKEditor instances the closing screen owns so
        // re-opening the same record builds a fresh editor (avoids stale-content
        // leaks / re-init on the same element). gcEditor flushes each editor to
        // its textarea before destroying.
        if (window.gcEditor) {
            try { gcEditor.destroyWithin(screen); } catch (e) {}
        }
        // Same story for quick-add: gcQuickAddInit parks its modal on
        // document.body (outside this screen), so removing the screen alone
        // leaks the modal — and its duplicate gcqa_* ids outlive the form
        // they belong to. Hand it back before the screen goes.
        if (window.gcQuickAdd && typeof window.gcQuickAdd.destroyWithin === 'function') {
            try { window.gcQuickAdd.destroyWithin(screen); } catch (e) {}
        }
        if (stack.length === 0) { document.body.classList.remove('screen-open'); }
        screen.classList.add('exit');
        var done = false;
        var remove = function () {
            if (done) { return; }
            done = true;
            if (screen.parentNode) { screen.parentNode.removeChild(screen); }
            var shouldRefresh = refresh || panelRefresh;
            if (!shouldRefresh) { return; }
            // If we're back on a child-list screen, re-fetch it WITH the
            // parent context (GET {parent}/{child}?i={ip}); otherwise
            // fall back to the generic top-level list refresh.
            var top = stack[stack.length - 1];
            var cw = top && top.querySelector('.va-mob.proto-app[data-model][data-ip]');
            if (cw) {
                refreshChildWrapper(cw);
            } else if (window.gcList && typeof window.gcList.refresh === 'function') {
                window.gcList.refresh();
            }
        };
        screen.addEventListener('transitionend', remove, { once: true });
        setTimeout(remove, 400); // fallback if transitionend doesn't fire
    }

    function fetchText(url, opts) {
        return fetch(url, opts).then(function (r) {
            if (r.status === 401) { window.location.reload(); return null; }
            return r.text().then(function (t) { return { status: r.status, text: t }; });
        });
    }

    // Re-fetch a child-list wrapper with its parent context and swap it
    // in place (the wrapper carries data-parent/data-model/data-ip from
    // childListBuilder). Used after a nested child save/delete.
    function refreshChildWrapper(cw) {
        var parent = cw.getAttribute('data-parent');
        var child = cw.getAttribute('data-model');
        var ip = cw.getAttribute('data-ip');
        if (!parent || !child) { return; }
        var url = SITE + parent + '/' + child +
            '?i=' + encodeURIComponent(ip == null ? '' : ip) +
            '&ui=' + encodeURIComponent(EDIT_UI);
        fetchText(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            if (!res) { return; }
            var tmp = document.createElement('div');
            tmp.innerHTML = res.text;
            var fresh = tmp.querySelector('.va-mob.proto-app[data-model]');
            if (fresh && cw.parentNode) {
                cw.parentNode.replaceChild(fresh, cw);
                // Seam for derived-data consumers (e.g. set_refresh_on_child_change
                // parent-field refresh): a child list was re-fetched after a
                // nested save/delete, so server-side recomputes are committed.
                try {
                    document.dispatchEvent(new CustomEvent('gc:list-refreshed', {
                        detail: { model: child, parent: parent, ip: ip }
                    }));
                } catch (e) { /* noop */ }
            }
        }).catch(function () { /* leave stale list on network error */ });
    }

    // opts.ip / opts.tp carry parent context when opening a NEW child
    // from a child list (so the server sets the FK). They are echoed
    // back on save (server contract §2 bindSave posts ip/tp/pc).
    function openEdit(model, pk, opts) {
        opts = opts || {};
        var pkStr = String(pk == null ? '' : pk);
        var ctxIp = opts.ip || '';
        // Re-opening the SAME entity already on screen should focus the
        // existing drawer, not stack a duplicate on top. Scoped to real
        // records (pk set): pk='' is an "add new" form and every add is a
        // distinct draft. Skipped on history restores (fromHistory), which
        // deliberately rebuild the stack from the URL.
        var pendingKey = null;
        if (pkStr !== '' && !opts.fromHistory) {
            var hit = findOpenEdit(model, pkStr, ctxIp, opts.ro);
            if (hit) {
                // Buried under newer screens → pop down to reveal it (prompts
                // to discard if any screen above is dirty). Already on top →
                // just nudge + refocus it. Either way: no second fetch/push.
                if (hit.index < stack.length - 1) {
                    popTo(hit.index + 1);
                } else {
                    focusScreen(hit.screen);
                }
                return;
            }
            pendingKey = model + ' ' + pkStr + ' ' + ctxIp;
            if (opts.ro) { pendingKey += '\u0000ro'; }
            if (pending[pendingKey]) { return; }  // dupe still in flight
            pending[pendingKey] = true;
        }
        var clearPending = function () {
            if (pendingKey) { delete pending[pendingKey]; pendingKey = null; }
        };
        var qs = '?ui=' + encodeURIComponent(EDIT_UI);
        if (opts.ip) { qs += '&ip=' + encodeURIComponent(opts.ip); }
        if (opts.tp) { qs += '&tp=' + encodeURIComponent(opts.tp); }
        if (opts.ro) { qs += '&ro=1'; }
        var url = SITE + model + '/edit/' + encodeURIComponent(pk == null ? '' : pk) + qs;
        fetchText(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            clearPending();
            if (!res) { return; }
            var s = push(res.text, model, pk, !opts.fromHistory);
            if (opts.ip) { s.setAttribute('data-ctx-ip', opts.ip); }
            if (opts.tp) { s.setAttribute('data-ctx-tp', opts.tp); }
            if (opts.ro) { s.setAttribute('data-ctx-ro', '1'); }
        }).catch(function () {
            clearPending();
            // Do NOT navigate to /edit/{pk} here — with the server-side
            // deep-link change that URL re-bootstraps openEdit, so a
            // failing fetch would loop forever. Surface a toast instead.
            toast('Couldn\'t open record');
        });
    }

    // Child list as a nested push-screen (server contract §7). The
    // child-list fragment carries its own .va-mob.proto-app[data-model]
    // (childListBuilder A4), so list.js + screens.js drive child
    // row→edit / search automatically. We only wrap it in a back-nav
    // header so the existing back handler ([data-screen-back]) pops it.
    function openChildList(parentModel, child, pid, title) {
        // Re-selecting a child tab whose list is already on the stack must
        // reveal that list, not push a second copy (mirrors openEdit's
        // findOpenEdit dedup). Without this, tabbing away and back — or
        // re-tapping the active tab — stacks duplicate child-list screens,
        // each with its own auto-save checkboxes.
        var key = parentModel + '\0' + child + '\0' + String(pid == null ? '' : pid);
        var hit = findOpenChildList(key);
        if (hit) {
            if (hit.index < stack.length - 1) { popTo(hit.index + 1); }
            else { focusScreen(hit.screen); }
            return Promise.resolve();
        }
        if (childListPending[key]) { return childListPending[key]; }  // dupe still in flight
        var url = SITE + parentModel + '/' + child +
            '?i=' + encodeURIComponent(pid == null ? '' : pid) +
            '&ui=' + encodeURIComponent(EDIT_UI);
        var p = fetchText(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            if (!res) { return; }
            // NtN / read-only cross-ref children aren't served by the
            // GET {Parent}/{Child} route (returns a 400 error page, not
            // a list fragment). Degrade gracefully instead of pushing a
            // broken screen. (Backend NtN list path = future work.)
            var looksValid = res.status >= 200 && res.status < 300 &&
                res.text.indexOf('va-mob proto-app') >= 0;
            if (!looksValid) {
                toast((title || child) + ' isn’t available here yet');
                return;
            }
            var safeTitle = window.gcCore.escapeHtml(title || child); // #32: full escape (was '<'-only)
            var html =
                '<div class="proto-form" style="position:absolute;inset:0">' +
                '<div class="form-nav">' +
                '<button type="button" class="nav-btn" data-screen-back>' +
                '<i class="ri-arrow-left-s-line"></i></button>' +
                '<div class="nav-title">' + safeTitle + '</div>' +
                '<span style="width:30px"></span></div>' +
                '<div class="form-scroll" style="position:relative">' +
                res.text + '</div></div>';
            push(html, child, '');
            var pushed = stack[stack.length - 1];
            if (pushed) { pushed.setAttribute('data-childlist-key', key); }
        }).catch(function () { toast('Could not load ' + (title || child)); });
        p = p.then(function () { delete childListPending[key]; },
                   function () { delete childListPending[key]; });
        childListPending[key] = p;
        return p;
    }

    // jQuery .serialize() equivalent restricted to the s='d' payload
    // fields inside the given screen's form.
    function serializeData(screen) {
        // Flush any CKEditor content back into its textarea before reading
        // values. The change:data handler in gcEditor keeps them current, but
        // sync here too so save (and net-change dirty tracking) never reads a
        // stale textarea.value. No-op when no editors are bound in this screen.
        if (window.gcEditor) { try { gcEditor.syncWithin(screen); } catch (e) {} }
        var params = new URLSearchParams();
        var fields = screen.querySelectorAll("input[s='d'], select[s='d'], textarea[s='d']");
        Array.prototype.forEach.call(fields, function (f) {
            if (f.disabled || !f.name) { return; }
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

    // The /update endpoint always returns HTTP 200 with a <script>
    // body. SUCCESS ⇒ contains sw_message(...) and no error_field.
    // VALIDATION FAILURE ⇒ contains error_field markers + alertb(t,msg)
    // and no sw_message. We don't eval the legacy jQuery; we mirror it
    // in vanilla: clear prior .error_field, mark each [v=FIELD], focus
    // the first, and surface the message — keeping the screen open.
    function applyValidationErrors(screen, text, env) {
        // A validation failure must always leave the Save button usable so the
        // user can correct and retry. Legacy bindSave disables it on click and
        // never re-enables on failure; the bindSave guard normally stops it from
        // binding under gcScreens, but re-enable here as a safety net for any
        // other path (e.g. a project's custom onReadyJs) that disabled it.
        Array.prototype.forEach.call(
            screen.querySelectorAll(".nav-save[disabled], [id^='save'][disabled]"),
            function (b) { b.removeAttribute('disabled'); b.disabled = false; }
        );
        // Clear prior error state (both the field-level marker and any
        // inline messages we injected last time).
        Array.prototype.forEach.call(
            screen.querySelectorAll('.error_field'),
            function (el) { el.classList.remove('error_field'); }
        );
        Array.prototype.forEach.call(
            screen.querySelectorAll('.field-error'),
            function (el) { el.parentNode && el.parentNode.removeChild(el); }
        );
        Array.prototype.forEach.call(
            screen.querySelectorAll('.form-banner.is-error'),
            function (el) { el.parentNode && el.parentNode.removeChild(el); }
        );
        var fields = {};
        var msg;
        if (env) {
            // S4 envelope path: fields + message arrive structured — no scraping.
            (env.fields || []).forEach(function (f) { if (f && f.name) { fields[f.name] = true; } });
            var em0 = (env.messages && env.messages[0]) || null;
            msg = em0 ? em0.text : null;
        } else {
            var re = /\[v=([A-Za-z0-9_]+)\]/g;
            var mm;
            while ((mm = re.exec(text)) !== null) { fields[mm[1]] = true; }
            var alertParsed = parseAlertb(text);
            msg = alertParsed ? alertParsed.msg : null;
        }
        var first = null;
        Object.keys(fields).forEach(function (v) {
            var el = screen.querySelector('[v=' + v + ']');
            if (!el) { return; }
            var span = el.querySelector('.select-label-span');
            var target = span || el;
            target.classList.add('error_field');
            // Find the .form-row container holding this field and
            // inject an inline error message below the field (so the
            // user sees WHICH field failed, not just a toast).
            var row = target.closest('.form-row') || target.parentNode;
            if (row && !row.querySelector('.field-error')) {
                var em = document.createElement('div');
                em.className = 'field-error';
                em.textContent = 'Required';
                row.appendChild(em);
                row.classList.add('has-error');
            }
            if (!first) { first = target; }
        });
        // Banner at the top of .sw-body so the user can't miss it.
        var body = screen.querySelector('.sw-body') || screen.querySelector('.form-scroll') || screen;
        var banner = document.createElement('div');
        banner.className = 'form-banner is-error';
        banner.textContent = msg
            ? ('Please fix: ' + msg)
            : 'Please fix the highlighted fields';
        if (body && body.firstChild) {
            body.insertBefore(banner, body.firstChild);
        } else if (body) {
            body.appendChild(banner);
        }
        if (first && typeof first.focus === 'function') {
            try { first.focus(); } catch (e) { /* non-focusable */ }
        }
        if (first && typeof first.scrollIntoView === 'function') {
            try { first.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) {}
        }
        toast(msg ? ('Please fix: ' + msg) : 'Please fix the highlighted fields');
    }

    // After a successful save: a base screen refreshes the underlying
    // list and closes via history; a nested child refreshes its wrapper
    // and pops directly.
    //
    // opts (optional):
    //   opts.refreshSelector — CSS selector for a specific child-list
    //     element to refresh (inside the now-top screen) instead of the
    //     page-level gcList.refresh(). Used when a panel/child drawer
    //     needs to refresh only a section rather than the whole list.
    //     Default behavior (page-level refresh) is unchanged when omitted.
    function popAfterSave(screen, opts) {
        // Accept the 1-arg form popAfterSave({opts}) — auto-resolve screen
        // from stack top. Also tolerate popAfterSave(null, opts) for callers
        // that don't have a screen handle.
        if (screen && typeof screen === 'object' && !screen.getAttribute) {
            // arg1 is opts; promote it, infer screen
            opts = screen;
            screen = null;
        }
        opts = opts || {};
        if (!screen) {
            // resolve from stack top
            screen = stack.length > 0 ? stack[stack.length - 1] : null;
        }
        if (!screen) {
            // no screen at all — nothing to pop; just return
            return;
        }
        var screenIsPanel = screen.getAttribute('data-model') === '__panel__';
        if (stack.indexOf(screen) === 0 && !screenIsPanel) {
            if (opts.refreshSelector) {
                // Refresh a specific element on the page (e.g. a child
                // list wrapper) instead of the full page-level list.
                var target = document.querySelector(opts.refreshSelector);
                if (target && target.getAttribute('data-ip')) {
                    refreshChildWrapper(target);
                } else if (window.gcList &&
                    typeof window.gcList.refresh === 'function') {
                    window.gcList.refresh();
                }
            } else if (window.gcList &&
                typeof window.gcList.refresh === 'function') {
                window.gcList.refresh();
            }
            stack.forEach(function (s) {
                s.setAttribute('data-dirty', '0');
            });
            history.back();
        } else if (screenIsPanel) {
            pop(false);  // panels never push history, so just pop
        } else {
            pop(true);
        }
    }

    function save(screen) {
        // One save in flight per screen: a double click/tap on a NEW record
        // POSTed twice (two inserts), and the second success popped a screen
        // already off the stack — popAfterSave() then closed the PARENT drawer
        // and threw away its unsaved edits.
        if (screen.__gcSaving) { return; }
        screen.__gcSaving = true;
        screen.setAttribute('data-gc-saving', '1');
        function done() {
            screen.__gcSaving = false;
            screen.removeAttribute('data-gc-saving');
        }
        var model = screen.getAttribute('data-model');
        var body = new URLSearchParams();
        body.set('d', serializeData(screen));
        body.set('ui', EDIT_UI);
        ['pc', 'je', 'jet', 'dialog'].forEach(function (k) { body.set(k, ''); });
        // Echo parent context so a NEW child gets its FK (contract §2).
        body.set('ip', screen.getAttribute('data-ctx-ip') || '');
        body.set('tp', screen.getAttribute('data-ctx-tp') || '');
        fetchText(SITE + model + '/update', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'X-GC-Envelope': '1'
            },
            body: body.toString()
        }).then(function (res) {
            done();
            if (!res) { return; }
            // The screen left the stack while the request ran (back, a second
            // response): its outcome must not pop anything else.
            if (stack.indexOf(screen) < 0) { return; }
            var text = res.text || '';
            // S4 — structured envelope (sent X-GC-Envelope): decode the outcome
            // directly. Falls through to the legacy scrape below when the server
            // emitted a <script> body (no envelope / kill-switch on / stale client).
            var env = parseEnvelope(text);
            if (env) {
                if (env.status === 'ok') {
                    toast('Saved');
                    popAfterSave(screen);
                } else if (env.status === 'error') {
                    applyValidationErrors(screen, text, env);
                } else {
                    var m0 = (env.messages && env.messages[0]) || {};
                    alertDialog(m0.title || 'Not saved', m0.text || 'The record could not be saved.');
                }
                return;
            }
            var ok = /sw_message\(/.test(text);            // success ONLY on explicit marker
            var hasFieldErrors = /error_field/.test(text);
            var hasAlert = /alertb\s*\(/.test(text);
            if (ok) {
                toast('Saved');
                popAfterSave(screen);
            } else if (hasFieldErrors) {
                applyValidationErrors(screen, text);       // unchanged path
            } else if (hasAlert) {
                // alertb-only refusal/validation on /update (no sw_message, no error_field):
                // surface the server message, DO NOT pop the drawer / claim Saved.
                var a = parseAlertb(text);
                alertDialog(
                    (a && a.title) || 'Not saved',
                    (a && a.msg) || 'The record could not be saved.'
                );
            } else if (res.status >= 200 && res.status < 300) {
                // Genuinely-empty 2xx legacy fragment (rare custom actions). Keep the
                // legacy optimistic behavior ONLY for the truly-bare case so we don't
                // regress custom actions, but no markers + no body is the only path here.
                toast('Saved');
                popAfterSave(screen);
            } else {
                toast('Save failed');
            }
        }).catch(function () { done(); toast('Save failed'); });
    }

    function closeSheet() {
        var s = sheets.pop();
        if (!s) { return; }
        s.sheet.classList.remove('open');
        s.dim.classList.remove('open');
        setTimeout(function () {
            if (s.dim.parentNode) { s.dim.parentNode.removeChild(s.dim); }
            if (s.sheet.parentNode) { s.sheet.parentNode.removeChild(s.sheet); }
        }, 300);
    }

    function openPicker(screen, label) {
        var name = label.getAttribute('data-name');
        if (!name) { return; }
        var hidden = screen.querySelector('input[name="' + name + '"]');
        var valueSpan = label.querySelector('.select-label-span');
        var placeholder = valueSpan ? (valueSpan.getAttribute('placeholder') || '') : name;
        var ul = label.querySelector('ul.select-element');
        if (!ul) { return; }
        var current = hidden ? hidden.value : '';
        var titleEl = label.closest('.form-row') &&
            label.closest('.form-row').querySelector('.lbl');
        var title = (titleEl && titleEl.textContent.trim()) || placeholder || name;

        var options = Array.prototype.slice.call(ul.children)
            .filter(function (li) { return li.tagName === 'LI'; })
            .map(function (li) {
                var isClear = li.classList.contains('default') ||
                    li.getAttribute('data-value') === 'default';
                return {
                    value: isClear ? '' : (li.getAttribute('data-value') || ''),
                    label: li.getAttribute('data-label') ||
                        (li.textContent || '').trim(),
                    isClear: isClear
                };
            });

        var dim = document.createElement('div');
        dim.className = 'proto-dim open';
        dim.style.zIndex = '120';

        var sheet = document.createElement('div');
        sheet.className = 'proto-sheet';
        sheet.style.zIndex = '121';
        var rowsHtml = options.map(function (o) {
            var sel = (o.value === current) ||
                (o.isClear && (current === '' || current == null));
            return '<div class="sheet-row' + (sel ? ' selected' : '') +
                '" data-val="' + encodeURIComponent(o.value) + '">' +
                '<span class="sr-name">' +
                (o.label ? window.gcCore.escapeHtml(o.label) : '') + // #32: full escape
                '</span>' + (sel ? '<i class="ri-check-line"></i>' : '') +
                '</div>';
        }).join('');
        sheet.innerHTML =
            '<div class="sheet-handle"></div>' +
            '<div class="sheet-head"><div class="sheet-title">' +
            window.gcCore.escapeHtml(title) + // #32: full escape
            '</div><button type="button" class="sheet-close" aria-label="Close">' +
            '<i class="ri-close-line"></i></button></div>' +
            '<div class="sheet-body">' + rowsHtml + '</div>';

        document.body.appendChild(dim);
        document.body.appendChild(sheet);
        // reflow → animate in
        // eslint-disable-next-line no-unused-expressions
        sheet.offsetWidth;
        sheet.classList.add('open');
        sheets.push({ sheet: sheet, dim: dim });

        var choose = function (val) {
            if (hidden) {
                hidden.value = val;
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (valueSpan) {
                var pick = options.filter(function (o) { return o.value === val; })[0];
                valueSpan.textContent = (pick && !pick.isClear)
                    ? pick.label : placeholder;
            }
            closeSheet();
        };
        sheet.addEventListener('click', function (ev) {
            if (ev.target.closest('.sheet-close')) { closeSheet(); return; }
            var row = ev.target.closest('.sheet-row');
            if (row) { choose(decodeURIComponent(row.getAttribute('data-val'))); }
        });
        dim.addEventListener('click', function () { closeSheet(); });
    }

    function onClick(e) {
        // List-row delete (designed confirm, vanilla POST). The link is
        // inside a .va-mob-row action cell, sibling to row→edit.
        var del = e.target.closest(".ac-delete-link, [j^='delete']");
        if (del) {
            var container = del.closest('.va-mob.proto-app[data-model]');
            var row = del.closest('.va-mob-row, [rid]');
            if (container && row) {
                e.preventDefault();
                e.stopPropagation();
                var model = container.getAttribute('data-model');
                var ui = container.getAttribute('data-ui') || '';
                var pk = pkFromRow(row);
                confirmDialog('Delete this record? This cannot be undone.')
                    .then(function (ok) { if (ok) { doDelete(model, pk, ui, container); } });
                return;
            }
            // Drawer-footer delete: button is inside a .proto-screen
            // (has data-model + data-pk) rather than a list .va-mob row.
            var drwScreen = del.closest('.proto-screen[data-model]');
            if (drwScreen) {
                var drwModel = drwScreen.getAttribute('data-model');
                var drwPk   = del.getAttribute('rid') || drwScreen.getAttribute('data-pk') || '';
                if (drwModel && drwPk) {
                    e.preventDefault();
                    e.stopPropagation();
                    confirmDialog('Delete this record? This cannot be undone.')
                        .then(function (ok) {
                            if (ok) {
                                doDelete(drwModel, drwPk, '', null);
                                pop(false); // close drawer after delete
                            }
                        });
                    return;
                }
            }
        }

        var screen = e.target.closest('.proto-screen');
        if (!screen || stack.indexOf(screen) === -1) { return; }

        // Bottom-sheet picker — used on mobile only. On desktop the
        // legacy jQuery SelectBox widget (bound on screen mount) opens
        // an anchored popover, so this would double-open + render
        // both the popover AND the full-width bottom sheet.
        var sbLabel = e.target.closest('.js-select-label');
        if (sbLabel && screen.contains(sbLabel) &&
            !(window.matchMedia && window.matchMedia('(min-width: 720px)').matches)) {
            e.preventDefault();
            e.stopPropagation();
            openPicker(screen, sbLabel);
            return;
        }

        // Child-list tab (legacy [j=conglet_{Parent}][p={Child}]
        // anchor) → push the child list as a nested screen instead of
        // the legacy jQuery-UI tab load. Parent pk = Id{Parent}[s='d'].
        var conglet = e.target.closest("[j^='conglet_'][p]");
        if (conglet && screen.contains(conglet)) {
            var j = conglet.getAttribute('j') || '';
            var parentModel = j.replace(/^conglet_/, '');
            var child = conglet.getAttribute('p');
            var pkInput = screen.querySelector("input[name='Id" + parentModel + "']");
            if (parentModel && child && pkInput) {
                e.preventDefault();
                e.stopPropagation();
                openChildList(parentModel, child, pkInput.value,
                    (conglet.getAttribute('data-title') || conglet.textContent || child).trim());
                return;
            }
        }

        // "+" on a child tab → open the child list, then push its add form
        // on top, so saving the new row pops back to the child LIST (not the
        // parent). Reuses the same openChildList + openEdit stack as the
        // manual tab → list → Add flow.
        var childAdd = e.target.closest("[j^='childadd_'][p]");
        if (childAdd && screen.contains(childAdd)) {
            e.preventDefault();
            e.stopPropagation();
            var jAdd = childAdd.getAttribute('j') || '';
            var addParent = jAdd.replace(/^childadd_/, '');
            var addChild = childAdd.getAttribute('p');
            var addPid = childAdd.getAttribute('ip');
            if (addPid == null || addPid === '') {
                var addPk = screen.querySelector("input[name='Id" + addParent + "']");
                addPid = addPk ? addPk.value : '';
            }
            var addTitle = (childAdd.getAttribute('data-title') || addChild).trim();
            var openP = openChildList(addParent, addChild, addPid, addTitle);
            var pushAdd = function () { openEdit(addChild, '', { ip: addPid, tp: addParent }); };
            if (openP && typeof openP.then === 'function') { openP.then(pushAdd); } else { pushAdd(); }
            return;
        }

        // "Add" inside a child-list screen → open a NEW child edit with
        // parent context (data-ip/data-tp on the child-list wrapper,
        // emitted by childListBuilder) so the new row gets its FK.
        // EXCEPT: the file-upload behaviors (is_file_upload_table /
        // upload_child) emit an Add button (id #pickfilesForm or
        // #pickfiles) managed by the gcUpload widget — hijacking its
        // click would suppress the browse dialog. The button always
        // lives inside a [id^="upload-"] container the upload widget
        // sets up, so detect it that way to cover both ids (and any
        // future variant).
        var addBtn = e.target.closest('.add-button, [id^="add"]');
        if (addBtn && addBtn.closest('[id^="upload-"]')) { addBtn = null; }
        if (addBtn && screen.contains(addBtn)) {
            var cw = screen.querySelector('.va-mob.proto-app[data-model]');
            if (cw && cw.getAttribute('data-ip')) {
                e.preventDefault();
                e.stopPropagation();
                openEdit(cw.getAttribute('data-model'), '', {
                    ip: cw.getAttribute('data-ip'),
                    tp: cw.getAttribute('data-tp')
                });
                return;
            }
        }

        // Breadcrumb crumb → jump back to that ancestor drawer.
        var crumb = e.target.closest('.nav-crumb');
        if (crumb && screen && screen.contains(crumb)) {
            e.preventDefault();
            popTo(parseInt(crumb.getAttribute('data-crumb-depth'), 10) + 1);
            return;
        }
        // Save.
        if (e.target.closest(".nav-save, [id^='save']")) {
            // Panels own their save flow — their onReadyJs scripts bind
            // the save button themselves (e.g. BulkUpdate's bind('click.saveBu')).
            // If we intercepted here we'd POST to __panel__/update → 404.
            // Let the event fall through so the panel's handler fires.
            if (screen.getAttribute('data-model') === '__panel__') { return; }
            e.preventDefault();
            save(screen);
            return;
        }
        // Back / cancel — confirm if there are unsaved edits.
        if (e.target.closest('.form-nav .nav-btn, .nav-back, [data-screen-back]')) {
            e.preventDefault();
            requestClose(screen);
            return;
        }
    }

    // Pop the top screen, asking to discard first if it is dirty.
    // Closing the BASE screen goes through history.back() so the URL
    // reverts to the list; onPopState then performs the DOM pop.
    // Panels (__panel__ model) are always popped directly — they are
    // transient and never pushed a history entry.
    function requestClose(screen) {
        screen = screen || stack[stack.length - 1];
        if (!screen) { return; }
        var isBase = (stack.indexOf(screen) === 0);
        var isPanel = screen.getAttribute('data-model') === '__panel__';
        var doClose = function () {
            if (isBase && !isPanel) {
                // Clear dirty first so onPopState does not re-prompt.
                stack.forEach(function (s) {
                    s.setAttribute('data-dirty', '0');
                });
                history.back();
            } else {
                pop(false);
            }
        };
        if (screen.getAttribute('data-dirty') === '1') {
            confirmDialog('Discard unsaved changes?', {
                confirmLabel: 'Discard', danger: true
            }).then(function (ok) { if (ok) { doClose(); } });
            return;
        }
        doClose();
    }

    // Reconcile the drawer stack with the URL after a history move
    // (browser back/forward, or our own history.back() on close).
    function onPopState(e) {
        var desired = (e && e.state && e.state.gcEdit) || null;

        // Browser Back over unsaved edits: popstate cannot be vetoed, so
        // re-assert the edit entry and ask before discarding.
        // Skip this for panels — panels are transient (no history entry
        // was pushed), so we just pop them directly below.
        var baseIsPanel = stack.length > 0 &&
            baseScreen().getAttribute('data-model') === '__panel__';
        if (!desired && stack.length && stackDirty() && !baseIsPanel) {
            var b = baseScreen();
            var bm = b.getAttribute('data-model');
            var bp = b.getAttribute('data-pk');
            history.pushState({ gcEdit: { model: bm, pk: bp } }, '',
                editUrl(bm, bp));
            confirmDialog('Discard unsaved changes?', {
                confirmLabel: 'Discard', danger: true
            }).then(function (ok) {
                if (ok) {
                    stack.forEach(function (s) {
                        s.setAttribute('data-dirty', '0');
                    });
                    history.back();
                }
            });
            return;
        }

        if (!desired) {                        // URL is the list
            while (stack.length) { pop(false); }
            return;
        }
        if (screenMatches(baseScreen(), desired.model, desired.pk)) {
            return;                            // already showing it
        }
        while (stack.length) { pop(false); }   // rebuild from the URL
        openEdit(desired.model, desired.pk, { fromHistory: true });
    }

    function onKey(e) {
        if (e.key !== 'Escape') { return; }
        if (sheets.length) { closeSheet(); return; }
        if (stack.length) { requestClose(); }
    }

    // gcScreens.openPanel(opts):
    //   opts.url    — required, the URL whose response is loaded as panel content
    //   opts.method — 'GET' (default) | 'POST'
    //   opts.data   — optional payload for POST: form-encoded string OR
    //                 plain object with scalar string/number values. Array
    //                 values are NOT supported (URLSearchParams.set coerces
    //                 via .toString, joining arrays with commas instead of
    //                 emitting multi-value params). Pre-encode if you need
    //                 array semantics.
    //   opts.title  — string shown in the panel's header (defaults to '')
    //   opts.refreshOnClose — boolean (default false); if true, the previous
    //                         screen's list refreshes when this panel pops
    // No URL/History push (panels are transient — closing returns to the
    // previous URL). Same overlay/stack semantics as openEdit.
    //
    // gcScreens.popAfterSave(screen, opts):
    //   opts.refreshSelector — optional CSS selector for the element to refresh
    //                          instead of the page-level list (used for child
    //                          lists inside a parent drawer)
    // Default behavior unchanged when opts is omitted.
    function openPanel(opts) {
        opts = opts || {};
        var url = opts.url;
        if (!url) { return; }
        var method = (opts.method || 'GET').toUpperCase();
        var title = opts.title != null ? String(opts.title) : '';
        var refreshOnClose = !!opts.refreshOnClose;

        var fetchOpts = {
            method: method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (method === 'POST') {
            fetchOpts.headers['Content-Type'] =
                'application/x-www-form-urlencoded; charset=UTF-8';
            if (opts.data) {
                if (typeof opts.data === 'string') {
                    fetchOpts.body = opts.data;
                } else {
                    var p = new URLSearchParams();
                    Object.keys(opts.data).forEach(function (k) {
                        p.set(k, opts.data[k]);
                    });
                    fetchOpts.body = p.toString();
                }
            }
        }

        fetchText(url, fetchOpts).then(function (res) {
            if (!res) { return; }
            var safeTitle = window.gcCore.escapeHtml(title); // #32: full escape (was '<'-only)
            var html =
                '<div class="proto-form" style="position:absolute;inset:0">' +
                '<div class="form-nav">' +
                '<button type="button" class="nav-btn" data-screen-back>' +
                '<i class="ri-arrow-left-s-line"></i></button>' +
                '<div class="nav-title">' + safeTitle + '</div>' +
                '<span style="width:30px"></span></div>' +
                '<div class="form-scroll" style="position:relative">' +
                res.text + '</div></div>';
            // Push without history sync — panels are transient and do
            // not claim a URL. We use a synthetic model/pk pair so the
            // stack entry has data-attributes but won't match any real
            // gcEdit history state. refreshOnClose is encoded as a
            // data-attribute so pop() can read it when the panel closes.
            var screen = push(html, '__panel__', '', false);
            if (refreshOnClose) {
                screen.setAttribute('data-panel-refresh', '1');
            }
        }).catch(function () { toast('Couldn\'t open panel'); });
    }

    function init() {
        // Capture phase: the injected fragment carries legacy markup;
        // capture beats any legacy stopPropagation (see shell.js).
        document.addEventListener('click', onClick, true);
        document.addEventListener('keydown', onKey, true);
        window.addEventListener('popstate', onPopState, false);
    }

    window.gcScreens = {
        openEdit: openEdit,
        openPanel: openPanel,
        popAfterSave: popAfterSave,
        toast: toast,
        confirm: confirmDialog,
        alert: alertDialog
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();
