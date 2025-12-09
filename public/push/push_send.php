<?php
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../config/db.php";

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// TUS CLAVES VAPID
$publicKey  = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";
$privateKey = "jXS61CRIWDzUi1MARa3qvP6upq2hH5GgucwcMNRPxE8";

$title = $_POST["title"];
$body  = $_POST["body"];
$url   = $_POST["url"] ?? "/public/app/panel_usuario.php";

$payload = json_encode([
    "title" => $title,
    "body"  => $body,
    "url"   => $url
]);

// Obtener todas las suscripciones
$res = $conn->query("SELECT * FROM suscripciones_push");

$webPush = new WebPush([
    "VAPID" => [
        "subject" => "mailto:admin@tusitio.com",
        "publicKey" => $publicKey,
        "privateKey" => $privateKey
    ]
]);

while ($row = $res->fetch_assoc()) {

    $sub = Subscription::create([
        "endpoint" => $row["endpoint"],
        "keys" => [
            "p256dh" => $row["keys_p256dh"],
            "auth"   => $row["keys_auth"]
        ]
    ]);

    $webPush->sendOneNotification($sub, $payload);
}

echo "OK";
