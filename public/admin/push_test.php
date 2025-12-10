<?php
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../config/db.php";

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// CLAVES VAPID
$publicKey  = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";
$privateKey = "jXS61CRIWDzUi1MARa3qvP6upq2hH5GgucwcMNRPxE8";

// Obtener una suscripción guardada
$sql = $conn->query("SELECT * FROM suscripciones_push ORDER BY id DESC LIMIT 1");
$sub = $sql->fetch_assoc();

if (!$sub) {
    die("❌ No hay suscripciones en la base de datos.");
}

$subscription = Subscription::create([
    "endpoint" => $sub["endpoint"],
    "keys" => [
        "p256dh" => $sub["p256dh"],
        "auth" => $sub["auth"],
    ],
]);

$webPush = new WebPush([
    "VAPID" => [
        "subject" => "mailto:admin@tudominio.com",
        "publicKey" => $publicKey,
        "privateKey" => $privateKey,
    ],
]);

$result = $webPush->sendOneNotification(
    $subscription,
    json_encode([
        "title" => "🔥 Fidelitipon está vivo",
        "body"  => "Notificación enviada correctamente desde Render.",
        "icon"  => "/assets/img/icon-192.png"
    ])
);

echo "<pre>";
var_dump($result);
echo "</pre>";
