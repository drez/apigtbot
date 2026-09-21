/**
 * gcPdfMenu — the standard with_pdf nav-title menu.
 *
 * The emitter (Parameters/with_pdf.php buildForm) calls
 *   gcPdfMenu.mount(formEl, { model, id, drive, driveBrowser })
 * from the edit form's onReadyJs; everything below is client behavior for
 * the markup this file builds. Endpoints are the emitted with_pdf service
 * actions (printable / pdfdownload / generatepdf / opengdrive). The two that
 * WRITE — generatepdf, opengdrive — are POSTed; only the reads are GETs.
 */
(function () {
    'use strict';

    function el(tag, cls, html) {
        var e = document.createElement(tag);
        if (cls) { e.className = cls; }
        if (html !== undefined) { e.innerHTML = html; }
        return e;
    }

    function toast(msg) {
        if (window.gcScreens && window.gcScreens.toast) { window.gcScreens.toast(msg); }
    }

    function fail(msg) {
        if (window.alertb) { alertb('PDF', msg); } else { toast(msg); } // #no-native-alert
    }

    function actionUrl(cfg, action) {
        return _SITE_URL + cfg.model + '/' + action + '?i=' + encodeURIComponent(cfg.id);
    }

    // The record id has to travel in the BODY on a POST: RouteHelper merges the
    // query string into the service args for GET only (getPOSTArgs reads the
    // parsed body), so a ?i= on a POST is simply not seen and the service
    // answers 404 "Record not found".
    function actionBody(cfg) {
        var b = new URLSearchParams();
        b.set('i', cfg.id);
        return b.toString();
    }

    // Every endpoint below that WRITES is reached with POST: the runtime refuses
    // a mutating action over GET (AuthyMiddleware::checkMutatingGet -> 405), and
    // the CSRF token is attached automatically by the window.fetch wrapper in
    // index.js for any non-GET same-origin request.
    function post(cfg, action) {
        return fetch(actionUrl(cfg, action), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: actionBody(cfg)
        });
    }

    function postJson(cfg, action) {
        return post(cfg, action).then(function (r) { return r.json(); });
    }

    function buildMenu(cfg) {
        var wrap = el('div', 'gc-pdf-menu nav-pdf');
        var btn = el('button', 'gc-pdf-btn header-controls',
            "<i class='ri-file-pdf-2-line'></i><span class='gc-pdf-label'>Printable</span><i class='ri-arrow-down-s-line gc-pdf-caret'></i>");
        btn.type = 'button';
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-expanded', 'false');
        btn.title = 'Printable';
        var dd = el('div', 'gc-pdf-dd');
        dd.hidden = true;

        var items = [
            ['view',   "ri-eye-line",            'View printable'],
            ['down',   "ri-download-2-line",     'Download'],
            ['regen',  "ri-refresh-line",        'Regenerate']
        ];
        if (cfg.drive) {
            items.push(['gdrive', 'ri-drive-line', 'Open gDrive']);
            if (cfg.driveBrowser) {
                items.push(['browser', 'ri-folder-open-line', 'Drive']);
            }
        }
        items.forEach(function (it) {
            var a = el('button', 'gc-pdf-item',
                "<i class='" + it[1] + "'></i><span>" + it[2] + '</span>');
            a.type = 'button';
            a.setAttribute('data-gc-pdf-action', it[0]);
            dd.appendChild(a);
        });

        wrap.appendChild(btn);
        wrap.appendChild(dd);

        function close() {
            dd.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            dd.hidden = !dd.hidden;
            btn.setAttribute('aria-expanded', dd.hidden ? 'false' : 'true');
        });
        // Self-removing: one document listener per mounted menu used to
        // outlive every closed drawer.
        document.addEventListener('click', function onDocClick(e) {
            if (!wrap.isConnected) { document.removeEventListener('click', onDocClick); return; }
            if (!dd.hidden && !wrap.contains(e.target)) { close(); }
        });

        dd.addEventListener('click', function (e) {
            var item = e.target.closest ? e.target.closest('[data-gc-pdf-action]') : null;
            if (!item) { return; }
            close();
            switch (item.getAttribute('data-gc-pdf-action')) {
                case 'view':
                    window.open(actionUrl(cfg, 'printable'), '_blank');
                    break;
                case 'down':
                    window.open(actionUrl(cfg, 'pdfdownload'), '_blank');
                    break;
                case 'regen':
                    // generatepdf REGENERATES the stored PDF, so it must be a
                    // POST. Open the tab synchronously (popup blockers only
                    // allow a window.open inside the click) and point it at the
                    // read-only pdfdownload once the regeneration has answered:
                    // that keeps the old UX — the fresh PDF inline in a new tab,
                    // with its real filename from Content-Disposition — which a
                    // blob: URL built from the POST body would lose.
                    var pdfTab = window.open('about:blank', '_blank');
                    post(cfg, 'generatepdf').then(function (r) {
                        if (!r.ok) { throw new Error('generate failed'); }
                        if (pdfTab) { pdfTab.location = actionUrl(cfg, 'pdfdownload'); }
                        toast('PDF regenerated');
                    }).catch(function () {
                        if (pdfTab) { pdfTab.close(); }
                        fail('PDF generation failed');
                    });
                    break;
                case 'gdrive':
                    // Open the tab synchronously so popup blockers allow it,
                    // then point it at the resolved Drive folder. res.url is the
                    // server-resolved Drive webViewLink; require an https URL
                    // before navigating (defence-in-depth against a poisoned
                    // redirect target).
                    var tab = window.open('about:blank', '_blank');
                    postJson(cfg, 'opengdrive').then(function (res) {
                        // tab is null when a popup blocker refused it.
                        if (res && res.status === 'success' && /^https:\/\//i.test(res.url || '')) {
                            if (tab) { tab.location = res.url; } else { fail('Allow pop-ups to open the Drive folder'); }
                        } else {
                            if (tab) { tab.close(); }
                            fail((res && res.message) || 'Drive folder unavailable');
                        }
                    }).catch(function () { if (tab) { tab.close(); } fail('Drive folder unavailable'); });
                    break;
                case 'browser':
                    window.open(_SITE_URL + 'DriveFile', '_blank');
                    break;
            }
        });

        return wrap;
    }

    window.gcPdfMenu = {
        /**
         * Mount the menu into the form's header row, before the Save button.
         * cfg: { model, id, drive, driveBrowser }
         */
        mount: function (form, cfg) {
            if (!form || !cfg || !cfg.id) { return; }
            // The .sw-drawer scaffold lives INSIDE the emitted <form> on a
            // direct page load, but wraps it when pushed as a screen — look
            // both ways.
            var drawer = form.querySelector('.sw-drawer') || form.closest('.sw-drawer');
            var header = drawer ? drawer.querySelector('.sw-header .form-nav') : null;
            if (!header) { return; }
            if (header.querySelector('.gc-pdf-menu')) { return; }
            var menu = buildMenu(cfg);
            var save = header.querySelector('.nav-save');
            if (save && save.parentNode) {
                save.parentNode.insertBefore(menu, save);
            } else {
                header.appendChild(menu);
            }
        }
    };
})();
