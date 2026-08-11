/**
 * gcPdfMenu — the standard with_pdf nav-title menu.
 *
 * The emitter (Parameters/with_pdf.php buildForm) calls
 *   gcPdfMenu.mount(formEl, { model, id, drive, driveBrowser })
 * from the edit form's onReadyJs; everything below is client behavior for
 * the markup this file builds. Endpoints are the emitted with_pdf service
 * actions (printable / pdfdownload / generatepdf / pdfbackup / opengdrive).
 *
 * Versioning contract: Regenerate replaces the current copy in place;
 * "Backup this version" renames it to _bak<N> — the only way versions
 * accumulate.
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

    function fetchJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); });
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
        document.addEventListener('click', function (e) {
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
                    window.open(actionUrl(cfg, 'generatepdf'), '_blank');
                    toast('PDF regenerated');
                    break;
                case 'gdrive':
                    // Open the tab synchronously so popup blockers allow it,
                    // then point it at the resolved Drive folder. res.url is the
                    // server-resolved Drive webViewLink; require an https URL
                    // before navigating (defence-in-depth against a poisoned
                    // redirect target).
                    var tab = window.open('about:blank', '_blank');
                    fetchJson(actionUrl(cfg, 'opengdrive')).then(function (res) {
                        if (res && res.status === 'success' && /^https:\/\//i.test(res.url || '')) {
                            tab.location = res.url;
                        } else {
                            tab.close();
                            fail((res && res.message) || 'Drive folder unavailable');
                        }
                    }).catch(function () { tab.close(); fail('Drive folder unavailable'); });
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
