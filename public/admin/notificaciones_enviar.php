<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if (!isset($_SESSION["admin_id"])) {
    die("No autorizado.");
}

$titulo  = trim($_POST["titulo"]);
$mensaje = trim($_POST["mensaje"]);
$url     = trim($_POST["url"]);

// Traer suscripciones desde la BD
$subs = $conn->query("SELECT endpoint, p256dh, auth FROM suscripciones_push");

// Config VAPID
$webpush = new WebPush([
    "VAPID" => [
        "subject" => "mailto:admin@fidelitipon.com",
        "publicKey" => "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI",
        "privateKey" => "jXS61CRIWDzUi1MARa3qvP6upq2hH5GgucwcMNRPxE8",
    ]
]);

$payload = json_encode([
    "title" => $titulo,
    "body"  => $mensaje,
    "url"   => $url
]);

$enviados = 0;

while ($s = $subs->fetch_assoc()) {
    $subscription = Subscription::create([
        "endpoint" => $s["endpoint"],
        "keys" => [
            "p256dh" => $s["p256dh"],
            "auth"   => $s["auth"]
        ]
    ]);

    $webpush->queueNotification($subscription, $payload);
    $enviados++;
}

foreach ($webpush->flush() as $report) {
    // Podemos gestionar errores aquí si quieres
}

header("Location: notificaciones.php?ok=$enviados");
exit;
