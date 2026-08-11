var CACHE_NAME = "gc-app-v1";

self.addEventListener("install", function(event) {
  // Activate immediately. This worker has no fetch handler — pages never load
  // assets through it — so a version swap is invisible to open pages and there
  // is nothing to prompt or reload for (it only serves push notifications).
  self.skipWaiting();
});

self.addEventListener("activate", function(event) {
  event.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(keys.map(function(cacheName) {
        if (cacheName !== CACHE_NAME) {
          console.log("Deleting old cache:", cacheName);
          return caches.delete(cacheName);
        }
      }));
    }).then(function() {
      return self.clients.claim();
    })
  );
});

// Kept for older project pwa.js copies that still post SKIP_WAITING from the
// legacy update prompt; harmless no-op once install() already skipped waiting.
self.addEventListener("message", function(event) {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

self.addEventListener("push", function(event) {
  var data = {};
  try {
    data = event.data ? event.data.json() : {};
  } catch(e) {
    data = { title: "New notification", body: event.data ? event.data.text() : "" };
  }

  // scope ends with "/" and is the app base (e.g. https://host/myproject/.admin/),
  // so build icon URLs from it rather than root-absolute "/public/..." which 404s
  // under a subpath deployment.
  var base = self.registration.scope;
  var title = data.title || "Notification";
  var options = {
    body: data.body || "",
    icon: base + "public/img/icon.png",
    badge: base + "public/img/icon.png",
    tag: "general",
    renotify: true
  };

  event.waitUntil(
    self.registration.showNotification(title, options).then(function() {
      if ("setAppBadge" in self.navigator) {
        return self.navigator.setAppBadge();
      }
    })
  );
});

self.addEventListener("notificationclick", function(event) {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then(function(clientList) {
      for (var i = 0; i < clientList.length; i++) {
        if (clientList[i].url && "focus" in clientList[i]) {
          return clientList[i].focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(self.registration.scope);
      }
    })
  );
});
