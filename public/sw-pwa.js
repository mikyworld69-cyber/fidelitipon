/* =========================================================================
   SERVICE WORKER UNIFICADO – FIDELITIPON
   Maneja: PWA + CACHE + OFFLINE + NOTIFICACIONES PUSH
========================================================================= */

// Nombre del caché
const CACHE_NAME = "fidelitipon-v1";

// Archivos que deseas cachear (IMPORTANTE que existan en /public)
const FILES_TO_CACHE = [
    "/",
    "/index.php",
    "/manifest.json",
    "/assets/img/icon-192.png",
    "/assets/img/icon-512.png",
];

// INSTALACIÓN DEL SERVICE WORKER
self.addEventListener("install", event => {
    console.log("SW instalado");

    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(FILES_TO_CACHE);
        })
    );

    self.skipWaiting();
});

// ACTIVACIÓN DEL SERVICE WORKER
self.addEventListener("activate", event => {
    console.log("SW activado");

    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );

    self.clients.claim();
});

// ESTRATEGIA DE CACHE: NETWORK FIRST CON FALLBACK A CACHE
self.addEventListener("fetch", event => {
    const request = event.request;

    // Evitamos cachear peticiones POST o no GET
    if (request.method !== "GET") return;

    event.respondWith(
        fetch(request)
            .then(response => {
                // Guardar copia en caché
                const respClone = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, respClone);
                });
                return response;
            })
            .catch(() => {
                // Si falla la red → intentamos cargar desde caché
                return caches.match(request);
            })
    );
});

/* =========================================================================
   NOTIFICACIONES PUSH
========================================================================= */

// Cuando llega un mensaje push del servidor
self.addEventListener("push", event => {
    console.log("Push recibido:", event.data ? event.data.text() : "sin datos");

    if (!event.data) return;

    let data;

    try {
        data = event.data.json();
    } catch (e) {
        data = { title: "Fidelitipon", body: event.data.text() };
    }

    const options = {
        body: data.body || "Tienes una nueva notificación",
        icon: "/assets/img/icon-192.png",
        badge: "/assets/img/icon-192.png",
        data: {
            url: data.url || "/"
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title || "Fidelitipon", options)
    );
});

// Cuando se hace clic en la notificación
self.addEventListener("notificationclick", event => {
    event.notification.close();

    const url = event.notification.data.url || "/";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
            // Abrir nueva ventana si no existe
            for (const client of windowClients) {
                if (client.url.includes(url) && "focus" in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});

/* =========================================================================
   FIN DEL SERVICE WORKER UNIFICADO
========================================================================= */
