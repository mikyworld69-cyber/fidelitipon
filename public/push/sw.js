self.addEventListener('push', event => {
    const data = event.data ? JSON.parse(event.data.text()) : {};

    const title = data.title || "Notificación";
    const options = {
        body: data.body || "",
        icon: data.icon || "/public/assets/img/icon-192.png",
        badge: data.badge || "/public/assets/img/icon-192.png",
        data: {
            url: data.url || "/public/app/panel_usuario.php"
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener("notificationclick", event => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
