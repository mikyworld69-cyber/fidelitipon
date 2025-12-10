document.addEventListener("DOMContentLoaded", async () => {
    if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
        console.log("Push no soportado");
        return;
    }

    const sw = await navigator.serviceWorker.register("/sw-pwa.js");
    console.log("Service Worker registrado:", sw);

    let permission = await Notification.requestPermission();
    if (permission !== "granted") {
        console.warn("Notificaciones denegadas");
        return;
    }

    const vapidPublicKey = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";

    const subscription = await sw.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
    });

    // Enviar suscripción al servidor
    await fetch("/push/push_subscribe.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(subscription)
    });

    console.log("Suscripción enviada al servidor.");
});

/* Convertir VAPID key */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}
