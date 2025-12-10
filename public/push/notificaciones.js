console.log("JS de notificaciones cargado.");

const PUBLIC_VAPID = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";

if ("serviceWorker" in navigator && "PushManager" in window) {
    
    navigator.serviceWorker.register("/sw-pwa.js")
    .then(reg => {
        console.log("SW registrado:", reg);

        return Notification.requestPermission().then(permission => {
            if (permission !== "granted") {
                console.log("Notificaciones no permitidas");
                return;
            }

            return reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: PUBLIC_VAPID
            });
        });
    })
    .then(sub => {
        if (!sub) return;
        console.log("Suscripción creada:", sub);

        return fetch("/push/guardar_suscripcion_push.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(sub)
        });
    })
    .catch(err => console.error("Error:", err));

} else {
    console.log("SW o Push no soportado.");
}
