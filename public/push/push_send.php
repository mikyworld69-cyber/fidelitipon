<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// Claves VAPID (las que me diste)
$publicKey  = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";
$privateKey = "jXS61CRIWDzUi1MARa3qvP6upq2hH5GgucwcMNRPxE8";

$payload = [
    "title" => $_POST["titulo"] ?? "Fidelitipon",
    "body"  => $_POST["mensaje"] ?? "Notificación",
    "url"   => $_POST["url"] ?? "/"
];

$payloadJson = json_encode($payload);

// Cargar todas las suscripciones
$res = $conn->query("SELECT endpoint, p256dh, auth FROM suscripciones_push");

$webPush = new WebPush([
    "VAPID" => [
        "subject" => "mailto:admin@fidelitipon.com",
        "publicKey" => $publicKey,
        "privateKey" => $privateKey
    ]
]);

$success = 0;

while ($row = $res->fetch_assoc()) {
    $sub = Subscription::create([
        "endpoint" => $row["endpoint"],
        "keys" => [
            "p256dh" => $row["p256dh"],
            "auth"   => $row["auth"]
        ]
    ]);

    $webPush->sendOneNotification($sub, $payloadJson);
    $success++;
}

echo "Notificaciones enviadas a $success suscriptores.";
