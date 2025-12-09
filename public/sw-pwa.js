const CACHE_NAME = "fidelitipon-v1";

const urlsToCache = [
    "/public/app/app.css",
    "/public/app/panel_usuario.php",
    "/public/manifest.json",
    "/public/icons/icon-192.png",
    "/public/icons/icon-512.png"
];

// INSTALACIÓN
self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        })
    );
});

// ACTIVACIÓN
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        })
    );
});

// FETCH
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});
