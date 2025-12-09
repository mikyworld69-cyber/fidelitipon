<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ===========================
// VALIDAR CAMPOS
// ===========================
$titulo  = trim($_POST["titulo"] ?? "");
$mensaje = trim($_POST["mensaje"] ?? "");
$destino = $_POST["destino"] ?? "";

if ($titulo === "" || $mensaje === "") {
    die("Faltan campos requeridos.");
}

// ===============================
// CLAVES VAPID (LAS TUYAS)
// ===============================
$publicKey  = "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI";
$privateKey = "jXS61CRIWDzUi1MARa3qvP6upq2hH5GgucwcMNRPxE8";

$auth = [
    "VAPID" => [
        "subject" => "mailto:admin@fidelitipon.com",
        "publicKey" => $publicKey,
        "privateKey" => $privateKey
    ]
];

$webPush = new WebPush($auth);

// ===============================
// OBTENER SUSCRIPCIONES SEGÚN DESTINO
// ===============================
$subs_sql = "";

if ($destino === "todos") {
    $subs_sql = "SELECT * FROM suscripciones_push";

} elseif ($destino === "usuario") {
    $usuario_id = intval($_POST["usuario_id"]);
    $subs_sql = "SELECT * FROM suscripciones_push WHERE usuario_id = {$usuario_id}";

} elseif ($destino === "comercio") {
    $comercio_id = intval($_POST["comercio_id"]);

    // Buscar cupones de ese comercio → usuarios
    $subs_sql = "
        SELECT sp.*
        FROM suscripciones_push sp
        JOIN usuarios u ON u.id = sp.usuario_id
        JOIN cupones c ON c.usuario_id = u.id
        WHERE c.comercio_id = {$comercio_id}
        GROUP BY sp.id
    ";
}

$result = $conn->query($subs_sql);

// ===============================
// ENVIAR NOTIFICACIONES
// ===============================
$total_enviados = 0;

while ($row = $result->fetch_assoc()) {

    $subscription = Subscription::create([
        "endpoint" => $row["endpoint"],
        "publicKey" => $row["p256dh"],
        "authToken" => $row["auth"],
        "contentEncoding" => "aes128gcm"
    ]);

    $payload = json_encode([
        "title" => $titulo,
        "body"  => $mensaje,
        "icon"  => "/public/icon.png"
    ]);

    try {
        $res = $webPush->sendOneNotification($subscription, $payload);
        $total_enviados++;
    } catch (Exception $e) {
        // Puedes registrar errores si quieres
    }
}

// ===============================
// GUARDAR HISTORIAL
// ===============================
$hist = $conn->prepare("
    INSERT INTO notificaciones (titulo, mensaje, fecha_envio, total_enviados)
    VALUES (?, ?, NOW(), ?)
");
$hist->bind_param("ssi", $titulo, $mensaje, $total_enviados);
$hist->execute();

// ===============================
// VOLVER AL PANEL
// ===============================
header("Location: notificaciones.php?ok=1");
exit;

?>
