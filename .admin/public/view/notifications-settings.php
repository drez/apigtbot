<?php
/**
 * Reusable push-notifications toggle (in-page control).
 *
 * `include` this anywhere you want a user-facing enable/disable control — e.g. an
 * Account or Settings page, or inside a tab. It drives the window.gc* API exposed
 * by public/js/pwa.js (gcNotificationsState / gcEnableNotifications /
 * gcDisableNotifications), so the underlying push wiring is shared.
 *
 * The `data-gc-notif-control` attribute below makes pwa.js automatically suppress
 * its floating pill on any page that includes this partial — so you never get two
 * notification UIs at once. "Enabled" means THIS browser/device is subscribed
 * (web push is inherently per-device).
 *
 * Requires: pwa.js loaded on the page (it is, via config/assets.php).
 */
?>
<div class="gc-notif-settings" style="max-width:560px;">
    <style>
        .gc-notif-settings .gc-notif-btn{background:#00d1b2;color:#fff;border:0;padding:10px 18px;
            border-radius:5px;font:600 14px/1.2 inherit;cursor:pointer;
            transition:background .15s,opacity .15s;}
        .gc-notif-settings .gc-notif-btn:hover{background:#00b89a;}
        .gc-notif-settings .gc-notif-btn.on{background:#1f7a3d;}
        .gc-notif-settings .gc-notif-btn.on:hover{background:#19632f;}
        .gc-notif-settings .gc-notif-btn[disabled]{opacity:.6;cursor:default;}
    </style>
    <p style="font-size:13px;color:#666;margin:0 0 14px 0;line-height:1.5;"><?= _('Turn on push notifications to get an alert on this device. Notifications are per-device, so enable them on each device you use.') ?></p>
    <button id="gcNotifControl" class="gc-notif-btn" type="button" data-gc-notif-control style="display:none;" onclick="gcNotifToggle()"><?= _('Enable notifications') ?></button>
    <p id="gcNotifUnsupported" style="display:none;font-size:13px;color:#888;margin:0;"><?= _('Notifications are not supported on this browser.') ?></p>
</div>
<script>
(function () {
    var I18N = {
        enable:  <?= json_encode(_('Enable notifications')) ?>,
        on:      <?= json_encode(_('Notifications on')) ?>,
        blocked: <?= json_encode(_('Notifications blocked in your browser')) ?>,
        working: <?= json_encode(_('Working…')) ?>
    };

    function render(state) {
        var btn = document.getElementById('gcNotifControl');
        var unsup = document.getElementById('gcNotifUnsupported');
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
            btn.textContent = I18N.blocked;
            return;
        }
        btn.disabled = false;
        if (state.subscribed) {
            btn.classList.add('on');
            btn.dataset.on = '1';
            btn.textContent = I18N.on;
        } else {
            btn.classList.remove('on');
            btn.dataset.on = '';
            btn.textContent = I18N.enable;
        }
    }

    function refresh() {
        if (typeof window.gcNotificationsState !== 'function') {
            render({ supported: false, permission: 'unsupported', subscribed: false });
            return;
        }
        window.gcNotificationsState().then(render).catch(function () {});
    }

    window.gcNotifToggle = function () {
        var btn = document.getElementById('gcNotifControl');
        if (!btn || btn.disabled) return;
        var isOn = btn.dataset.on === '1';
        btn.disabled = true;
        btn.textContent = I18N.working;
        var action = isOn ? window.gcDisableNotifications : window.gcEnableNotifications;
        Promise.resolve(typeof action === 'function' ? action() : false)
            .then(function () { refresh(); })
            .catch(function () { refresh(); });
    };

    // Render state once the service worker is active (so gcNotificationsState can
    // resolve a registration); fall back to a direct render if SW is unsupported.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(refresh).catch(function () { refresh(); });
    } else {
        refresh();
    }
}());
</script>
