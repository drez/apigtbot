<?php $gcGoogleClientId = (string) ($_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: ''); ?>
<div id="accountPage" style="padding:20px;max-width:600px;">
    <style>
        .acct-btn{background:var(--mint,#00d1b2);color:var(--on-accent,#fff);border:0;padding:10px 18px;border-radius:5px;
            font:600 14px/1.2 inherit;cursor:pointer;transition:background .15s,opacity .15s;}
        .acct-btn:hover{background:var(--mint-600,#00b89a);}
        .acct-btn.on{background:#1f7a3d;}
        .acct-btn.on:hover{background:#19632f;}
        .acct-btn[disabled]{opacity:.6;cursor:default;}
        /* Single clean row that scrolls when the tabs don't fit (mobile + the
           600px card): touch swipes it, mouse wheel scrolls it horizontally
           (JS below), and a thin scrollbar gives a visible affordance. */
        .acct-tabs{flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;scrollbar-width:thin;}
        .acct-tabs::-webkit-scrollbar{height:5px;}
        .acct-tabs::-webkit-scrollbar-thumb{background:var(--line,#dde);border-radius:5px;}
        .acct-tab{flex:0 0 auto;white-space:nowrap;}
    </style>
    <h2 style="margin:0 0 20px 0;"><?= _('My Account') ?></h2>

    <div id="accountMsg" style="display:none;margin-bottom:15px;padding:10px 14px;border-radius:4px;font-size:14px;"></div>

    <div class="acct-tabs" style="border-bottom:2px solid var(--line,#dde);margin-bottom:0;display:flex;gap:0;">
        <button class="acct-tab active" data-tab="email" onclick="acctTab('email')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--mint-700,#667eea);border-bottom:2px solid var(--mint-700,#667eea);margin-bottom:-2px;"><?= _('Email') ?></button>
        <button class="acct-tab" data-tab="password" onclick="acctTab('password')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Password') ?></button>
        <button class="acct-tab" data-tab="language" onclick="acctTab('language')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Language') ?></button>
        <button class="acct-tab" data-tab="location" onclick="acctTab('location')" style="display:none;padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Location') ?></button>
        <button class="acct-tab" data-tab="notifications" onclick="acctTab('notifications')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Notifications') ?></button>
        <button class="acct-tab" data-tab="theme" onclick="acctTab('theme')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Theme') ?></button>
        <button class="acct-tab" data-tab="connected" onclick="acctTab('connected')" style="padding:9px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-mute,#888);border-bottom:2px solid transparent;margin-bottom:-2px;"><?= _('Connected accounts') ?></button>
    </div>

    <div style="background:var(--surface,#fff);border:1px solid var(--line,#dde);border-top:none;border-radius:0 0 6px 6px;padding:20px;">

        <!-- Email tab -->
        <div id="tab-email">
            <div style="margin-bottom:10px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('New Email') ?></label>
                <input type="email" id="acctEmail" autocomplete="off" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);" placeholder="<?= _('Enter new email') ?>">
            </div>
            <button class="acct-btn" onclick="accountSave('email')"><?= _('Update Email') ?></button>
        </div>

        <!-- Password tab -->
        <div id="tab-password" style="display:none;">
            <div style="margin-bottom:10px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('Current Password') ?></label>
                <input type="password" id="acctCurrentPwd" autocomplete="new-password" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);" placeholder="<?= _('Enter current password') ?>">
            </div>
            <div style="margin-bottom:10px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('New Password') ?></label>
                <input type="password" id="acctNewPwd" autocomplete="new-password" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);" placeholder="<?= _('At least 8 characters') ?>">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('Confirm New Password') ?></label>
                <input type="password" id="acctConfirmPwd" autocomplete="new-password" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);" placeholder="<?= _('Repeat new password') ?>">
            </div>
            <button class="acct-btn" onclick="accountSave('password')"><?= _('Update Password') ?></button>
        </div>

        <!-- Language tab -->
        <div id="tab-language" style="display:none;">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('Preferred Language') ?></label>
                <select id="acctLanguage" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);">
                    <option value="en_US"><?= _('English (en_US)') ?></option>
                    <option value="fr_CA"><?= _('French (fr_CA)') ?></option>
                </select>
            </div>
            <button class="acct-btn" onclick="accountSave('language')"><?= _('Update Language') ?></button>
        </div>

        <!-- Location tab (only shown when the project's authy model has the
             location columns — see the GET prefill below) -->
        <div id="tab-location" style="display:none;">
            <p style="font-size:13px;color:var(--text-mid,#666);margin:0 0 14px 0;line-height:1.5;"><?= _('Your home base. The Add Time location picker finds it under "home" / "maison".') ?></p>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;color:var(--text-mid,#666);margin-bottom:4px;"><?= _('Location Address') ?></label>
                <input type="text" id="acctLocAddress" autocomplete="off" style="width:100%;padding:8px 10px;border:1px solid var(--line-hard,#ccc);border-radius:4px;font-size:14px;box-sizing:border-box;background:var(--surface,#fff);color:var(--text,#333);" placeholder="<?= _('Search an address…') ?>">
                <input type="hidden" id="acctLocLat">
                <input type="hidden" id="acctLocLng">
            </div>
            <button class="acct-btn" onclick="accountSave('location')"><?= _('Update Location') ?></button>
        </div>

        <!-- Notifications tab -->
        <div id="tab-notifications" style="display:none;">
            <p style="font-size:13px;color:var(--text-mid,#666);margin:0 0 14px 0;line-height:1.5;"><?= _('Turn on push notifications to get an alert on this device. Notifications are per-device, so enable them on each device you use.') ?></p>
            <button id="acctNotifBtn" class="acct-btn" type="button" data-gc-notif-control onclick="acctToggleNotif()" style="display:none;"><?= _('Enable notifications') ?></button>
            <p id="acctNotifUnsupported" style="display:none;font-size:13px;color:var(--text-mute,#888);margin:0;"><?= _('Notifications are not supported on this browser.') ?></p>
        </div>

        <!-- Theme tab -->
        <div id="tab-theme" style="display:none;">
            <p style="font-size:13px;color:var(--text-mid,#666);margin:0 0 14px 0;line-height:1.5;"><?= _('Pick a theme. The preview applies immediately; Save makes it yours on every device.') ?></p>
            <div id="acctThemeGrid" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <?php foreach ([
                    'mint'       => ['#f6f9fc', '#ffffff', '#00d1b2', 'Mint'],
                    'ink'        => ['#0f1828', '#16213a', '#00d1b2', 'Ink Dark'],
                    'slate'      => ['#2b3039', '#343b45', '#6ea8e6', 'Slate'],
                    'dusk'       => ['#2c2c2c', '#363636', '#b39cd0', 'Dusk'],
                    'indigo'     => ['#f5f6fb', '#ffffff', '#6366f1', 'Indigo'],
                    'terracotta' => ['#faf6f0', '#fffdf9', '#c2552c', 'Terracotta'],
                    'graphite'   => ['#f4f4f5', '#ffffff', '#18181b', 'Graphite'],
                ] as $tName => $tSw): ?>
                <button type="button" class="acct-theme-card" data-theme-pick="<?= $tName ?>" onclick="acctPickTheme('<?= $tName ?>')"
                    style="cursor:pointer;border:2px solid var(--line,#dde);border-radius:8px;padding:8px;background:<?= $tSw[0] ?>;width:104px;text-align:center;">
                    <span style="display:flex;gap:4px;justify-content:center;margin-bottom:6px;">
                        <span style="width:22px;height:22px;border-radius:50%;background:<?= $tSw[1] ?>;border:1px solid rgba(0,0,0,.12);"></span>
                        <span style="width:22px;height:22px;border-radius:50%;background:<?= $tSw[2] ?>;"></span>
                    </span>
                    <span style="font-size:12px;font-weight:600;color:<?= in_array($tName, ['ink', 'slate', 'dusk'], true) ? '#c7d2e3' : '#444' ?>;"><?= _($tSw[3]) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <button class="acct-btn" onclick="accountSave('theme')"><?= _('Update Theme') ?></button>
        </div>

        <!-- Connected accounts tab -->
        <div id="tab-connected" style="display:none;">
            <p style="font-size:13px;color:var(--text-mid,#666);margin:0 0 14px 0;line-height:1.5;"><?= _('Link your Google account to sign in with Google.') ?></p>
            <div id="acctGoogleLinked" style="display:none;align-items:center;gap:12px;">
                <span><?= _('Connected as') ?> <strong id="acctGoogleEmail"></strong></span>
                <button class="acct-btn" onclick="acctGoogleUnlink()"><?= _('Unlink') ?></button>
            </div>
            <div id="acctGoogleUnlinked" style="display:none;">
                <?php if ($gcGoogleClientId !== ''): ?>
                <div class="login-google" style="display:flex;justify-content:center;width:100%;">
                    <div id="acctGoogleBtn" class="g_id_signin"></div>
                </div>
                <?php
                    // The button is rendered via the JS API (google.accounts.id
                    // .initialize + renderButton in the script below), NOT the HTML
                    // #g_id_onload auto-render: g_id_onload auto-displays One Tap, which
                    // kept re-popping the "Sign in with Google" notice even after the
                    // account was already linked. We never call prompt(), so there is no
                    // One Tap — only the explicit button. Mirrors the login page
                    // (AuthyForm::getGoogleSignin).
                    //
                    // NO SRI integrity hash on gsi/client: it is an unversioned, Google-rotated
                    // script; a pinned hash would break sign-in. Likewise NO crossorigin attr:
                    // accounts.google.com serves gsi/client without Access-Control-Allow-Origin,
                    // so a CORS-mode load is blocked by the browser (verified 2026-06-12).
                ?>
                <script<?= gcNonceAttr() ?> src="https://accounts.google.com/gsi/client" async defer referrerpolicy="no-referrer"></script>
                <?php else: ?>
                <em style="color:var(--text-mute,#888);"><?= _('Google sign-in is not configured for this project (set GOOGLE_CLIENT_ID in the project .env).') ?></em>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script<?= gcNonceAttr() ?>>
(function () {
    // Mouse wheel over the tab bar scrolls it horizontally, so the off-screen
    // tabs are reachable on desktop without a horizontal-trackpad gesture
    // (touch already swipes; the thin scrollbar is the visual cue).
    (function () {
        var strip = document.querySelector('.acct-tabs');
        if (!strip) { return; }
        strip.addEventListener('wheel', function (e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && strip.scrollWidth > strip.clientWidth) {
                strip.scrollLeft += e.deltaY;
                e.preventDefault();
            }
        }, { passive: false });
    })();
    var apiUrl = _SITE_URL + 'api/v<?= API_VERSION ?>/Account';
    var acctGoogleClientId = <?= json_encode($gcGoogleClientId) ?>;
    var I18N = {
        pleaseEnterEmail: <?= json_encode(_('Please enter an email address')) ?>,
        saved: <?= json_encode(_('Saved')) ?>,
        genericError: <?= json_encode(_('An error occurred')) ?>,
        requestFailed: <?= json_encode(_('Request failed')) ?>
    };
    var NOTIF_I18N = {
        enable:  <?= json_encode(_('Enable notifications')) ?>,
        on:      <?= json_encode(_('Notifications on')) ?>,
        blocked: <?= json_encode(_('Notifications blocked in your browser')) ?>,
        working: <?= json_encode(_('Working…')) ?>
    };

    function renderNotif(state) {
        var btn = document.getElementById('acctNotifBtn');
        var unsup = document.getElementById('acctNotifUnsupported');
        if (!btn) return;
        if (!state.supported) {
            btn.style.display = 'none';
            unsup.style.display = 'block';
            return;
        }
        unsup.style.display = 'none';
        btn.style.display = 'inline-block';
        if (state.permission === 'denied') {
            btn.disabled = true;
            btn.classList.remove('on');
            btn.dataset.on = '';
            btn.textContent = NOTIF_I18N.blocked;
            return;
        }
        btn.disabled = false;
        if (state.subscribed) {
            btn.classList.add('on');
            btn.dataset.on = '1';
            btn.textContent = NOTIF_I18N.on;
        } else {
            btn.classList.remove('on');
            btn.dataset.on = '';
            btn.textContent = NOTIF_I18N.enable;
        }
    }

    function refreshNotif() {
        if (typeof window.gcNotificationsState !== 'function') {
            renderNotif({ supported: false, permission: 'unsupported', subscribed: false });
            return;
        }
        window.gcNotificationsState().then(renderNotif).catch(function () {});
    }

    window.acctToggleNotif = function () {
        var btn = document.getElementById('acctNotifBtn');
        if (!btn || btn.disabled) return;
        var isOn = btn.dataset.on === '1';
        btn.disabled = true;
        btn.textContent = NOTIF_I18N.working;
        var action = isOn ? window.gcDisableNotifications : window.gcEnableNotifications;
        Promise.resolve(typeof action === 'function' ? action() : false)
            .then(function () { refreshNotif(); })
            .catch(function () { refreshNotif(); });
    };

    window.acctTab = function (name) {
        ['email', 'password', 'language', 'location', 'notifications', 'theme', 'connected'].forEach(function (t) {
            var tab = document.querySelector('.acct-tab[data-tab="' + t + '"]');
            var pane = document.getElementById('tab-' + t);
            var active = t === name;
            pane.style.display = active ? '' : 'none';
            tab.style.color = active ? 'var(--mint-700,#667eea)' : 'var(--text-mute,#888)';
            tab.style.borderBottomColor = active ? 'var(--mint-700,#667eea)' : 'transparent';
            tab.style.fontWeight = '600';
        });
        // Bind the address widget only once the pane is visible: Leaflet maps
        // initialized inside a display:none container render blank tiles.
        if (name === 'location' && window.gcLocationField) {
            window.gcLocationField.create({ input: 'acctLocAddress', lat: 'acctLocLat', lng: 'acctLocLng' });
        }
    };

    var acctTheme = document.documentElement.getAttribute('data-theme') || 'mint';
    function paintThemeCards() {
        document.querySelectorAll('.acct-theme-card').forEach(function (c) {
            c.style.borderColor = c.dataset.themePick === acctTheme ? 'var(--mint, #00d1b2)' : 'var(--line, #dde)';
            c.style.boxShadow = c.dataset.themePick === acctTheme ? '0 0 0 2px var(--mint-100, #d4f3ed)' : 'none';
        });
    }
    window.acctPickTheme = function (t) {
        acctTheme = t;
        if (t === 'mint') { document.documentElement.removeAttribute('data-theme'); }
        else { document.documentElement.setAttribute('data-theme', t); }
        paintThemeCards();
    };
    paintThemeCards();

    function acctPatch(payload, onOk) {
        fetch(apiUrl, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.status === 'success') { onOk(res); }
                else { showMsg((res.errors || []).join(' ') || I18N.genericError, true); }
            })
            .catch(function () { showMsg(I18N.requestFailed, true); });
    }
    window.acctGoogleLink = function (resp) {
        acctPatch({ google_credential: resp.credential }, function () { window.location.reload(); });
    };
    window.acctGoogleUnlink = function () {
        confirm(<?= json_encode(_('Unlink your Google account?')) ?>, function () {
            acctPatch({ google_unlink: 1 }, function () { window.location.reload(); });
        });
    };

    // Render the "Continue with Google" button via the JS API and NEVER call
    // google.accounts.id.prompt(): that (and the HTML #g_id_onload form) is what
    // pops One Tap, which kept re-appearing after the account was already linked.
    // Only invoked while unlinked. Polls until the async gsi/client script loads.
    function acctRenderGoogleBtn() {
        if (!acctGoogleClientId) { return true; }
        var el = document.getElementById('acctGoogleBtn');
        if (!el) { return true; }
        if (!(window.google && window.google.accounts && window.google.accounts.id)) { return false; }
        if (el.dataset.gRendered) { return true; }
        window.google.accounts.id.initialize({
            client_id: acctGoogleClientId,
            callback: window.acctGoogleLink,
            auto_select: false,
            cancel_on_tap_outside: true
        });
        el.innerHTML = '';
        window.google.accounts.id.renderButton(el, {
            type: 'standard', shape: 'rectangular', theme: 'outline',
            text: 'continue_with', size: 'large', width: 400
        });
        el.dataset.gRendered = '1';
        return true;
    }
    function acctWaitAndRenderGoogle() {
        var tries = 0;
        (function poll() {
            if (acctRenderGoogleBtn()) { return; }
            if (tries++ < 100) { setTimeout(poll, 50); }
        })();
    }

    function showMsg(msg, isError) {
        var el = document.getElementById('accountMsg');
        el.textContent = msg;
        el.style.display = 'block';
        el.style.background = isError ? '#fde8e8' : '#e8f5e9';
        el.style.color = isError ? '#c00' : '#2a7a2a';
        el.style.border = '1px solid ' + (isError ? '#f5c6cb' : '#c3e6cb');
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.display = 'none'; }, 4000);
    }

    // Load current values
    fetch(apiUrl, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.status === 'success' && res.data) {
                // Set the value (not just the placeholder): shows the real
                // current email, and a filled field keeps browser credential
                // autofill from dropping the saved login username into it.
                document.getElementById('acctEmail').value = res.data.email || '';
                var langSel = document.getElementById('acctLanguage');
                if (res.data.language) {
                    for (var i = 0; i < langSel.options.length; i++) {
                        if (langSel.options[i].value === res.data.language) {
                            langSel.selectedIndex = i;
                            break;
                        }
                    }
                }
                if (res.data.theme) { acctTheme = res.data.theme; paintThemeCards(); }
                if (res.data.location_supported) {
                    var locTabBtn = document.querySelector('.acct-tab[data-tab="location"]');
                    if (locTabBtn) { locTabBtn.style.display = ''; }
                    document.getElementById('acctLocAddress').value = res.data.location_address || '';
                    document.getElementById('acctLocLat').value = (res.data.location_lat === 0 || res.data.location_lat) ? res.data.location_lat : '';
                    document.getElementById('acctLocLng').value = (res.data.location_lng === 0 || res.data.location_lng) ? res.data.location_lng : '';
                }
                var linked = !!res.data.google_linked;
                document.getElementById('acctGoogleLinked').style.display = linked ? 'flex' : 'none';
                document.getElementById('acctGoogleUnlinked').style.display = linked ? 'none' : '';
                document.getElementById('acctGoogleEmail').textContent = res.data.google_email || '';
                // Only build the Google button when NOT linked — and via the JS API,
                // so One Tap is never prompted (no stray "sign in with Google" notice).
                if (!linked) { acctWaitAndRenderGoogle(); }
            }
        })
        .catch(function () {});

    window.accountSave = function (section) {
        var payload = {};

        if (section === 'email') {
            var email = document.getElementById('acctEmail').value.trim();
            if (!email) { showMsg(I18N.pleaseEnterEmail, true); return; }
            payload.email = email;
        } else if (section === 'password') {
            payload.current_password = document.getElementById('acctCurrentPwd').value;
            payload.new_password     = document.getElementById('acctNewPwd').value;
            payload.confirm_password = document.getElementById('acctConfirmPwd').value;
        } else if (section === 'language') {
            payload.language = document.getElementById('acctLanguage').value;
        } else if (section === 'theme') {
            payload.theme = acctTheme;
        } else if (section === 'location') {
            payload.location_address = document.getElementById('acctLocAddress').value.trim();
            payload.location_lat     = document.getElementById('acctLocLat').value;
            payload.location_lng     = document.getElementById('acctLocLng').value;
        }

        fetch(apiUrl, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.status === 'success') {
                showMsg((res.messages && res.messages[0]) || I18N.saved, false);
                if (section === 'password') {
                    document.getElementById('acctCurrentPwd').value = '';
                    document.getElementById('acctNewPwd').value = '';
                    document.getElementById('acctConfirmPwd').value = '';
                }
                if (section === 'language') {
                    setTimeout(function () { window.location.reload(); }, 800);
                }
                if (section === 'theme') {
                    try { localStorage.setItem('gcTheme', acctTheme); } catch (e) {}
                }
            } else {
                var errs = (res.errors || []).join(' ');
                showMsg(errs || I18N.genericError, true);
            }
        })
        .catch(function () { showMsg(I18N.requestFailed, true); });
    };

    // Render notification state. Prefer the service-worker-ready path: it guarantees
    // pwa.js's window.gc* helpers can resolve a registration, and avoids a brief
    // "not supported" flash from rendering before the SW is active.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(refreshNotif).catch(function () { refreshNotif(); });
    } else {
        refreshNotif();
    }
}());
</script>
