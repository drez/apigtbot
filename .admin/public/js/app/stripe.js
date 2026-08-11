/**
 * gcStripeMenu — with_stripe record actions (drift-synced by gc build).
 *
 * The emitter (Parameters/with_stripe.php buildForm) calls
 *   gcStripeMenu.mount(form, { model, pk, modes })
 * from the edit form's onReadyJs — the same mount(form, cfg) shape as
 * gcPdfMenu (see pdfmenu.js), mounted at the same insertion point
 * (.sw-drawer .sw-header .form-nav, just before .nav-save). modes is drawn
 * from the with_stripe schema param and contains 'payment' and/or
 * 'subscription'.
 *
 * Menu items (nav "Payment" dropdown):
 *  - "Request payment link" (always): GET <model>/stripecheckout?i=<pk> ->
 *    {status:'ok', url, pay_url, payment_id} -> alertb with the pay_url
 *    (app-hosted short link) + a copy-to-clipboard button, and an "Open"
 *    link pointed at the raw Stripe-hosted `url`.
 *  - "Start subscription…" (only when modes includes 'subscription'):
 *    there is no dedicated JSON price-list endpoint — StripePrice is a
 *    plain GoatCheese table — so this reuses the generic
 *    GET <Table>?ui=... HTML-fragment list contract app/list.js already
 *    consumes for any model, parses the returned rows
 *    (Classes/include/getList.php emits every row as
 *    `.va-mob-row[rid=<pk>]`, the one stable cross-table convention) into a
 *    selectable list, then on pick GET
 *    <model>/stripecheckout?i=<pk>&price=<id>.
 *  - "Charge saved card" (only when modes includes 'payment'):
 *    gcScreens.confirm() first, then GET <model>/stripecharge?i=<pk> ->
 *    {status, payment_status, message} -> alertb the result.
 *  - "Payment status" (always): GET <model>/stripestatus?i=<pk> ->
 *    {status:'ok', payments:[{payment_id,status,amount,currency,
 *    receipt_url,error}]} -> alertb a rendered ledger table.
 *
 * All five actions are plain GET, so — like gcPdfMenu — no CSRF header is
 * attached (the CSRF gate only covers state-changing session-auth
 * POST/PUT/PATCH/DELETE; window.fetch is still the page's globally wrapped
 * fetch from index.js, same as gcPdfMenu relies on). Every server response
 * is JSON; on error ({status:'error', message}, possibly a non-2xx HTTP
 * status) the message surfaces via alertb. All dialogs go through
 * alertb()/gcScreens.confirm() — NEVER native alert/confirm/prompt.
 */
(function () {
    'use strict';

    function el(tag, cls, html) {
        var e = document.createElement(tag);
        if (cls) { e.className = cls; }
        if (html !== undefined) { e.innerHTML = html; }
        return e;
    }

    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toast(msg) {
        if (window.gcScreens && window.gcScreens.toast) { window.gcScreens.toast(msg); }
    }

    function fail(msg) {
        // alertb()/alertDialog renders string msgs via textContent (never
        // HTML) — pass the raw string; esc()'ing here would double-escape
        // (e.g. an apostrophe would show as the literal "&#39;").
        if (window.alertb) { alertb('Payment', msg); } else { toast(msg); } // #no-native-alert
    }

    function actionUrl(cfg, action, extra) {
        var url = _SITE_URL + cfg.model + '/' + action + '?i=' + encodeURIComponent(cfg.pk);
        if (extra) {
            Object.keys(extra).forEach(function (k) {
                if (extra[k] !== undefined && extra[k] !== null && extra[k] !== '') {
                    url += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(extra[k]);
                }
            });
        }
        return url;
    }

    function fetchJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); });
    }

    // ---------------------------------------------------------- payment link

    function showLink(res) {
        var payUrl = res.pay_url || '';
        var openUrl = res.url || payUrl;
        var box = el('div', 'gc-stripe-linkbox',
            "<p class='gc-stripe-payurl'>" + esc(payUrl) + "</p>"
            + "<button type='button' class='gc-stripe-copy' data-gc-stripe-copy='" + esc(payUrl) + "'>"
            + "<i class='ri-file-copy-line'></i> Copy link</button> "
            + "<a class='gc-stripe-openlink' href='" + esc(openUrl) + "' target='_blank' rel='noopener'>"
            + "<i class='ri-external-link-line'></i> Open</a>");
        alertb('Payment link', box);
    }

    function requestLink(cfg) {
        fetchJson(actionUrl(cfg, 'stripecheckout')).then(function (res) {
            if (!res || res.status !== 'ok') {
                fail((res && res.message) || 'Could not create a payment link');
                return;
            }
            showLink(res);
        }).catch(function () { fail('Could not create a payment link'); });
    }

    // -------------------------------------------------------- charge saved

    function chargeSaved(cfg) {
        var run = function () {
            fetchJson(actionUrl(cfg, 'stripecharge')).then(function (res) {
                if (!res || res.status !== 'ok') {
                    fail((res && res.message) || 'Charge failed');
                    return;
                }
                // Plain string — alertb() renders it via textContent, so no
                // <p> wrapper (would show as literal tags) and no esc() (would
                // double-escape entities).
                alertb('Charge saved card', res.message || ('Payment ' + (res.payment_status || 'submitted')));
            }).catch(function () { fail('Charge failed'); });
        };
        if (window.gcScreens && typeof window.gcScreens.confirm === 'function') {
            window.gcScreens.confirm('Charge the saved card on file for this record?', { confirmLabel: 'Charge', danger: false })
                .then(function (ok) { if (ok) { run(); } });
        } else {
            run();
        }
    }

    // ------------------------------------------------------- payment status

    function showStatus(payments) {
        if (!payments || !payments.length) {
            // Plain string — textContent renders it fine without a <p> wrapper.
            alertb('Payment status', 'No payments yet.');
            return;
        }
        var rows = payments.map(function (p) {
            var amt = (typeof p.amount === 'number') ? p.amount.toFixed(2) : esc(p.amount);
            var receipt = p.receipt_url
                ? ("<a href='" + esc(p.receipt_url) + "' target='_blank' rel='noopener'>Receipt</a>")
                : '';
            var err = p.error ? ("<div class='gc-stripe-err'>" + esc(p.error) + "</div>") : '';
            return "<tr><td>#" + esc(p.payment_id) + "</td><td>" + esc(p.status) + "</td>"
                + "<td>" + esc(amt) + ' ' + esc((p.currency || '').toUpperCase()) + "</td>"
                + "<td>" + receipt + err + "</td></tr>";
        }).join('');
        // Build a real DOM node (el()) — same pattern as showLink() — so
        // alertb()'s Node branch appends it as markup instead of textContent
        // rendering the tags as literal text.
        var table = el('table', 'gc-stripe-status',
            "<thead><tr><th>#</th><th>Status</th><th>Amount</th><th></th></tr></thead><tbody>"
            + rows + "</tbody>");
        alertb('Payment status', table);
    }

    function checkStatus(cfg) {
        fetchJson(actionUrl(cfg, 'stripestatus')).then(function (res) {
            if (!res || res.status !== 'ok') {
                fail((res && res.message) || 'Could not load payment status');
                return;
            }
            showStatus(res.payments || []);
        }).catch(function () { fail('Could not load payment status'); });
    }

    // ------------------------------------------------- subscription picker

    // No dedicated JSON price-list endpoint exists for StripePrice — reuse
    // the plain admin list fragment (same GET <Table>?ui=... contract
    // app/list.js consumes) and read the row's `rid` (primary key, JSON
    // encoded like every emitted list row) + its visible text.
    function fetchPriceRows() {
        return fetch(_SITE_URL + 'StripePrice?ui=gcStripePricePicker', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.text(); }).then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var rows = Array.prototype.slice.call(doc.querySelectorAll('.va-mob-row[rid]'));
            return rows.map(function (row) {
                var pk = row.getAttribute('rid');
                try { pk = JSON.parse(pk); } catch (e) { /* already a bare scalar */ }
                var label = (row.textContent || '').replace(/\s+/g, ' ').trim();
                return { id: pk, label: label };
            }).filter(function (p) { return p.id !== null && p.id !== undefined && p.id !== '' && p.label !== ''; });
        });
    }

    function startSubscription(cfg, priceId) {
        fetchJson(actionUrl(cfg, 'stripecheckout', { price: priceId })).then(function (res) {
            if (!res || res.status !== 'ok') {
                fail((res && res.message) || 'Could not start subscription checkout');
                return;
            }
            showLink(res);
        }).catch(function () { fail('Could not start subscription checkout'); });
    }

    function pickPriceAndCheckout(cfg) {
        fetchPriceRows().then(function (prices) {
            if (!prices.length) {
                fail('No prices found — add one under Stripe > Prices first');
                return;
            }
            var box = el('div', 'gc-stripe-pricepicker');
            prices.forEach(function (p) {
                var b = el('button', 'gc-stripe-price-item', esc(p.label));
                b.type = 'button';
                b.__gcStripeCfg = cfg;
                b.setAttribute('data-gc-stripe-price', String(p.id));
                box.appendChild(b);
            });
            alertb('Start subscription', box);
        }).catch(function () { fail('Could not load prices'); });
    }

    // Delegated listener for interactive content injected into the alertb
    // dialog (copy-to-clipboard button, price-picker rows) — the dialog is
    // plain DOM, so a document-level listener catches clicks wherever
    // gcScreens renders it, same delegation style as gcPdfMenu's own
    // dropdown.
    document.addEventListener('click', function (e) {
        var copyBtn = e.target.closest ? e.target.closest('[data-gc-stripe-copy]') : null;
        if (copyBtn) {
            var url = copyBtn.getAttribute('data-gc-stripe-copy') || '';
            if (url && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () { toast('Copied'); }).catch(function () {});
            }
            return;
        }
        var priceBtn = e.target.closest ? e.target.closest('[data-gc-stripe-price]') : null;
        if (priceBtn && priceBtn.__gcStripeCfg) {
            startSubscription(priceBtn.__gcStripeCfg, priceBtn.getAttribute('data-gc-stripe-price'));
        }
    });

    // ------------------------------------------------------------- nav menu

    function buildMenu(cfg) {
        var wrap = el('div', 'gc-stripe-menu nav-pdf');
        var btn = el('button', 'gc-stripe-btn header-controls',
            "<i class='ri-bank-card-2-line'></i><span class='gc-stripe-label'>Payment</span><i class='ri-arrow-down-s-line gc-stripe-caret'></i>");
        btn.type = 'button';
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-expanded', 'false');
        btn.title = 'Payment';
        var dd = el('div', 'gc-stripe-dd');
        dd.hidden = true;

        var items = [
            ['link', 'ri-links-line', 'Request payment link']
        ];
        if (cfg.modes.indexOf('subscription') !== -1) {
            items.push(['subscribe', 'ri-repeat-line', 'Start subscription…']);
        }
        if (cfg.modes.indexOf('payment') !== -1) {
            items.push(['charge', 'ri-bank-card-line', 'Charge saved card']);
        }
        items.push(['status', 'ri-list-check-2', 'Payment status']);

        items.forEach(function (it) {
            var a = el('button', 'gc-stripe-item',
                "<i class='" + it[1] + "'></i><span>" + it[2] + '</span>');
            a.type = 'button';
            a.setAttribute('data-gc-stripe-action', it[0]);
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
            var item = e.target.closest ? e.target.closest('[data-gc-stripe-action]') : null;
            if (!item) { return; }
            close();
            switch (item.getAttribute('data-gc-stripe-action')) {
                case 'link':
                    requestLink(cfg);
                    break;
                case 'subscribe':
                    pickPriceAndCheckout(cfg);
                    break;
                case 'charge':
                    chargeSaved(cfg);
                    break;
                case 'status':
                    checkStatus(cfg);
                    break;
            }
        });

        return wrap;
    }

    window.gcStripeMenu = {
        /**
         * Mount the menu into the form's header row, before the Save button.
         * cfg: { model, pk, modes }
         */
        mount: function (form, cfg) {
            if (!form || !cfg || !cfg.pk) { return; }
            cfg.modes = Array.isArray(cfg.modes) ? cfg.modes : [];
            // The .sw-drawer scaffold lives INSIDE the emitted <form> on a
            // direct page load, but wraps it when pushed as a screen — look
            // both ways (same as gcPdfMenu).
            var drawer = form.querySelector('.sw-drawer') || form.closest('.sw-drawer');
            var header = drawer ? drawer.querySelector('.sw-header .form-nav') : null;
            if (!header) { return; }
            if (header.querySelector('.gc-stripe-menu')) { return; }
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
