(function() {
  "use strict";

  var siteUrl = typeof _SITE_URL !== "undefined" ? _SITE_URL : "/";
  var swPath = siteUrl + "sw.js";
  var swRegistration = null;    // registration cache for getRegistration(); feeds gcEnable/Disable/NotificationsState

  // Clear app badge when user opens the app
  if ("clearAppBadge" in navigator) {
    navigator.clearAppBadge().catch(function() {});
  }

  /**
   * Convert a URL-safe base64 string to a Uint8Array for use as applicationServerKey.
   */
  function urlBase64ToUint8Array(base64String) {
    var padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    var base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; i++) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  /**
   * Resolve the active service-worker registration at call time.
   * `swRegistration` is only populated after register() resolves; relying on it
   * directly means an early click (or a failed/slow registration) silently no-ops.
   * navigator.serviceWorker.ready resolves once a worker is active and controlling.
   */
  function getRegistration() {
    if (swRegistration) return Promise.resolve(swRegistration);
    if (!("serviceWorker" in navigator)) return Promise.resolve(null);
    return navigator.serviceWorker.ready.then(function(reg) {
      swRegistration = reg;
      return reg;
    }).catch(function() {
      return null;
    });
  }

  /**
   * Whether the server configured push at all (a VAPID public key is emitted).
   * With no key, a subscription is impossible — so the UI should treat push as
   * unavailable instead of offering a toggle that prompts then silently fails.
   */
  function pushConfigured() {
    return typeof _VAPID_PUBLIC_KEY !== "undefined" && !!_VAPID_PUBLIC_KEY;
  }

  /**
   * Subscribe to push notifications and send the subscription to the server.
   */
  // Returns Promise<boolean> — true once a subscription exists.
  function subscribeToPush(registration) {
    var vapidKey = typeof _VAPID_PUBLIC_KEY !== "undefined" ? _VAPID_PUBLIC_KEY : "";
    if (!vapidKey) return Promise.resolve(false);

    return registration.pushManager.getSubscription().then(function(existingSub) {
      if (existingSub) {
        // Already subscribed — re-send to server in case it was lost
        sendSubscriptionToServer(existingSub);
        return true;
      }

      return registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey)
      }).then(function(sub) {
        sendSubscriptionToServer(sub);
        return true;
      }).catch(function(err) {
        console.warn("Push subscription failed:", err);
        return false;
      });
    }).catch(function(err) {
      // e.g. atob() throwing on a malformed _VAPID_PUBLIC_KEY would otherwise be an unhandled rejection
      console.warn("Push subscription setup failed:", err);
      return false;
    });
  }

  function sendSubscriptionToServer(sub) {
    fetch(siteUrl + "push/subscribe", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(sub.toJSON())
    }).catch(function(err) {
      console.warn("Failed to send subscription to server:", err);
    });
  }

  /**
   * Subscribe to push only if the user has ALREADY granted permission.
   * Does not prompt — auto-prompting for notifications without a user gesture is
   * penalized by browsers (auto-deny / abusive-permission UI) and a denial is sticky.
   * Safe to call on every page load.
   */
  function autoSubscribeIfGranted(registration) {
    if (!("PushManager" in window)) return;
    if (!("Notification" in window)) return;
    if (Notification.permission === "granted") {
      subscribeToPush(registration).catch(function(err) {
        console.warn("autoSubscribeIfGranted: unexpected error:", err);
      });
    }
  }

  /**
   * Explicit opt-in. Call this from a user gesture (e.g. an "Enable notifications"
   * button) via window.gcEnableNotifications(). Returns a Promise<boolean>.
   */
  function enableNotifications(registration) {
    if (!("PushManager" in window) || !("Notification" in window)) {
      return Promise.resolve(false);
    }
    // Don't prompt for permission when we couldn't subscribe anyway (no VAPID key).
    if (!pushConfigured()) {
      return Promise.resolve(false);
    }
    if (Notification.permission === "granted") {
      return subscribeToPush(registration);
    }
    if (Notification.permission === "denied") {
      return Promise.resolve(false);
    }
    return Notification.requestPermission().then(function(permission) {
      if (permission === "granted") {
        return subscribeToPush(registration);
      }
      return false;
    });
  }

  // Enable (prompt + subscribe) — call from a user gesture.
  window.gcEnableNotifications = function() {
    return getRegistration().then(function(reg) {
      if (!reg) return false;
      return enableNotifications(reg);
    });
  };

  /**
   * Unsubscribe from push and tell the server to drop the subscription.
   * Resolves true if a subscription was removed.
   */
  function unsubscribeFromPush() {
    return getRegistration().then(function(reg) {
      if (!reg) return false;
      return reg.pushManager.getSubscription().then(function(sub) {
        if (!sub) return false;
        var endpoint = sub.endpoint;
        return sub.unsubscribe().then(function() {
          fetch(siteUrl + "push/unsubscribe", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "same-origin",
            body: JSON.stringify({ endpoint: endpoint })
          }).catch(function(err) {
            console.warn("Failed to notify server of unsubscribe:", err);
          });
          return true;
        });
      });
    }).catch(function(err) {
      console.warn("Unsubscribe failed:", err);
      return false;
    });
  }

  window.gcDisableNotifications = function() {
    return unsubscribeFromPush();
  };

  /**
   * State reader for UI (e.g. the Account page). Resolves an object describing
   * push availability for THIS browser/device — no prompting, safe to call anytime.
   * @returns Promise<{supported:boolean, permission:string, subscribed:boolean}>
   *   permission is "granted" | "denied" | "default" | "unsupported".
   */
  window.gcNotificationsState = function() {
    if (!("serviceWorker" in navigator) || !("PushManager" in window) || !("Notification" in window)) {
      return Promise.resolve({ supported: false, permission: "unsupported", subscribed: false });
    }
    // Server hasn't configured push (no VAPID key) — nothing the user can do, so
    // report unavailable. Keeps the pill hidden and the Account control from
    // offering a dead toggle.
    if (!pushConfigured()) {
      return Promise.resolve({ supported: false, permission: "unsupported", subscribed: false });
    }
    return getRegistration().then(function(reg) {
      if (!reg) {
        return { supported: true, permission: Notification.permission, subscribed: false };
      }
      return reg.pushManager.getSubscription().then(function(sub) {
        return { supported: true, permission: Notification.permission, subscribed: !!sub };
      });
    }).catch(function() {
      return { supported: true, permission: Notification.permission, subscribed: false };
    });
  };

  // Notifications toggle — a small fixed pill; the default, zero-setup UI.
  // Drives the window.gc* API above (so it shares the getRegistration() fix).
  // Suppressed automatically when the host page provides its own control
  // (any element marked [data-gc-notif-control], e.g. an Account-settings
  // toggle), or globally when window.gcNotifPillOff === true.
  (function() {
    if (window.gcNotifPillOff === true) return;
    if (!("serviceWorker" in navigator)) return;
    if (!("PushManager" in window)) return;
    if (!("Notification" in window)) return;

    var btn = null;
    var busy = false;

    function styleText() {
      return [
        '#gc-notif-toggle{',
        'position:fixed;right:16px;bottom:16px;z-index:9998;',
        'display:inline-flex;align-items:center;gap:6px;',
        'padding:8px 13px;border:0;border-radius:20px;cursor:pointer;',
        'font:500 13px/1 system-ui,-apple-system,"Segoe UI",sans-serif;',
        'color:#fff;background:#2d2d2d;box-shadow:0 2px 8px rgba(0,0,0,0.25);',
        'transition:background .15s,opacity .15s}',
        '#gc-notif-toggle:hover{background:#000}',
        '#gc-notif-toggle.gc-on{background:#1f7a3d}',
        '#gc-notif-toggle.gc-on:hover{background:#19632f}',
        '#gc-notif-toggle[disabled]{cursor:default;opacity:.6}',
        '#gc-notif-toggle .gc-bell{font-size:15px;line-height:1}',
        '@media print{#gc-notif-toggle{display:none}}'
      ].join('');
    }

    function render(state) {
      if (!btn) return;
      if (!state || !state.supported) { btn.style.display = "none"; return; }
      btn.style.display = "";
      if (state.permission === "denied") {
        btn.className = "";
        btn.disabled = true;
        btn.title = "Notifications are blocked in your browser settings";
        btn.innerHTML = '<span class="gc-bell">🔕</span><span>Notifications blocked</span>';
        return;
      }
      btn.disabled = busy;
      if (state.subscribed) {
        btn.className = "gc-on";
        btn.title = "Notifications are on — click to turn off";
        btn.innerHTML = '<span class="gc-bell">🔔</span><span>Notifications on</span>';
      } else {
        btn.className = "";
        btn.title = "Click to enable notifications";
        btn.innerHTML = '<span class="gc-bell">🔔</span><span>Enable notifications</span>';
      }
    }

    function refresh() {
      if (typeof window.gcNotificationsState !== "function") return;
      window.gcNotificationsState().then(render).catch(function() {});
    }

    function onClick() {
      if (busy || btn.disabled) return;
      busy = true;
      btn.disabled = true;
      window.gcNotificationsState().then(function(state) {
        return state.subscribed ? window.gcDisableNotifications() : window.gcEnableNotifications();
      }).then(function() {
        busy = false;
        refresh();
      }).catch(function() {
        busy = false;
        refresh();
      });
    }

    function init() {
      // Only on authenticated app pages (skip login / public views).
      if (!document.querySelector(".app-topbar, .content-wrapper, .left-panel")) return;
      // Host page provides its own in-page notifications control — no pill.
      if (document.querySelector("[data-gc-notif-control]")) return;

      var style = document.createElement("style");
      style.textContent = styleText();
      document.head.appendChild(style);

      btn = document.createElement("button");
      btn.id = "gc-notif-toggle";
      btn.type = "button";
      btn.addEventListener("click", onClick);
      document.body.appendChild(btn);

      refresh();
      // Re-check once the service worker is active (state is only reliable then).
      navigator.serviceWorker.ready.then(refresh).catch(function() {});
    }

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
    } else {
      init();
    }
  })();

  // Pull-to-refresh on mobile
  (function() {
    var THRESHOLD = 120;
    var DEADZONE = 45;
    var MAX_START_Y = 72;
    var indicator = null;
    var startY = 0;
    var pulling = false;
    var committed = false;
    var refreshing = false;
    var thresholdReached = false;

    function getStyle() {
      return [
        '#ptr-indicator{',
        'position:fixed;top:0;left:0;width:100%;height:0;',
        'display:flex;align-items:center;justify-content:center;',
        'overflow:hidden;z-index:9999;pointer-events:none;',
        'background:rgba(255,255,255,0.95);',
        'box-shadow:0 2px 6px rgba(0,0,0,0.12);',
        'transition:background 0.2s}',
        '#ptr-indicator .ptr-spinner{',
        'width:24px;height:24px;border:2px solid #e0e0e0;',
        'border-top-color:#888;border-radius:50%;',
        'transition:border-color 0.2s,transform 0.1s}',
        '#ptr-indicator.ptr-ready .ptr-spinner{border-top-color:#111}',
        '#ptr-indicator.ptr-triggered .ptr-spinner{',
        'border-color:#ccc;border-top-color:#333;',
        'animation:ptr-spin 0.6s linear infinite}',
        '@keyframes ptr-spin{to{transform:rotate(360deg)}}'
      ].join('');
    }

    function getScrollTop() {
      var cw = document.querySelector('.content-wrapper');
      if (cw) return cw.scrollTop;
      return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop;
    }

    function getScrollEl() {
      return document.querySelector('.content-wrapper') || document.documentElement;
    }

    function init() {
      var style = document.createElement('style');
      style.textContent = getStyle();
      document.head.appendChild(style);

      indicator = document.createElement('div');
      indicator.id = 'ptr-indicator';
      indicator.innerHTML = '<div class="ptr-spinner"></div>';
      document.body.appendChild(indicator);

      // Suppress native overscroll/PTR where supported
      var el = getScrollEl();
      el.style.overscrollBehaviorY = 'contain';

      document.addEventListener('touchstart', onTouchStart, { passive: true });
      document.addEventListener('touchmove', onTouchMove, { passive: false });
      document.addEventListener('touchend', onTouchEnd, { passive: true });
      document.addEventListener('touchcancel', onTouchEnd, { passive: true });
    }

    function onTouchStart(e) {
      if (refreshing) return;
      if (!e.touches || e.touches.length !== 1) {
        pulling = false;
        return;
      }
      startY = e.touches[0].clientY;
      pulling = getScrollTop() <= 1 && startY <= MAX_START_Y;
      committed = false;
      thresholdReached = false;
    }

    function onTouchMove(e) {
      if (!pulling || refreshing) return;
      if (getScrollTop() > 1) {
        pulling = false;
        committed = false;
        thresholdReached = false;
        indicator.classList.remove('ptr-ready');
        indicator.style.height = '0';
        return;
      }
      var dy = e.touches[0].clientY - startY;
      if (dy <= 0) { committed = false; thresholdReached = false; indicator.classList.remove('ptr-ready'); indicator.style.height = '0'; return; }
      if (dy < DEADZONE) return; // not committed yet — don't block native touch
      committed = true;
      e.preventDefault();
      var pull = dy - DEADZONE;
      var h = Math.min(pull * 0.6, THRESHOLD);
      indicator.style.height = h + 'px';
      var rot = (h / THRESHOLD) * 270;
      indicator.querySelector('.ptr-spinner').style.transform = 'rotate(' + rot + 'deg)';
      var nowReached = h >= THRESHOLD * 0.95;
      if (nowReached && !thresholdReached) {
        thresholdReached = true;
        indicator.classList.add('ptr-ready');
        if (navigator.vibrate) navigator.vibrate(30);
      } else if (!nowReached && thresholdReached) {
        thresholdReached = false;
        indicator.classList.remove('ptr-ready');
      }
    }

    function onTouchEnd() {
      if (!pulling || refreshing) return;
      pulling = false;
      if (committed && thresholdReached) {
        refreshing = true;
        indicator.classList.remove('ptr-ready');
        indicator.classList.add('ptr-triggered');
        indicator.style.height = '50px';
        setTimeout(function() { location.reload(); }, 500);
      } else {
        indicator.style.height = '0';
        indicator.classList.remove('ptr-ready');
      }
    }

    if ('ontouchstart' in window) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    }
  })();

  if ("serviceWorker" in navigator) {
    // Register service worker
    // Updates apply silently: sw.js skipWaiting()s on install and claim()s on
    // activate. No prompt and no reload — the worker has no fetch handler, so
    // the page never depends on which version is active.
    navigator.serviceWorker.register(swPath).then(function(registration) {
      swRegistration = registration;

      // If a worker stuck around in "waiting" (left by the old prompt-based
      // flow before this file dropped it), activate it now.
      if (registration.waiting) {
        registration.waiting.postMessage({ type: "SKIP_WAITING" });
      }

      // If notifications were previously granted, (re)subscribe. ready resolves
      // once a worker is active (first install included).
      navigator.serviceWorker.ready.then(function(reg) {
        autoSubscribeIfGranted(reg);
      }).catch(function() {});
    }).catch(function(error) {
      console.error("Service Worker registration failed:", error);
    });
  }
})();
