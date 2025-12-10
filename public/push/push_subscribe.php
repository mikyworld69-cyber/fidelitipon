<?php
require_once __DIR__ . '/../../config/db.php';

header("Content-Type: application/json");

// Leer datos enviados desde JS
$body = file_get_contents("php://input");

if (!$body) {
    echo json_encode(["error" => "No se recibió suscripción"]);
    exit;
}

$data = json_decode($body, true);

// Validación
if (!isset($data["endpoint"])) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$endpoint = $data["endpoint"];
$p256dh = $data["keys"]["p256dh"] ?? "";
$auth   = $data["keys"]["auth"] ?? "";

// GUARDAR / ACTUALIZAR suscripción
$stmt = $conn->prepare("
    INSERT INTO suscripciones_push (endpoint, p256dh, auth)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        p256dh = VALUES(p256dh),
        auth   = VALUES(auth)
");

$stmt->bind_param("sss", $endpoint, $p256dh, $auth);
$stmt->execute();

echo json_encode(["success" => true]);
