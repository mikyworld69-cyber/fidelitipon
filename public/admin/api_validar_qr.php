<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';

// Verificar login admin
if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No autorizado."]);
    exit;
}

// Obtener código del cupón
$codigo = $_GET["codigo"] ?? $_POST["codigo"] ?? null;

if (!$codigo) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No se recibió un código."]);
    exit;
}

$codigo = trim($codigo);

// 1) Buscar cupón
$sql = $conn->prepare("
    SELECT c.*, com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.codigo = ?
");
$sql->bind_param("s", $codigo);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón no encontrado."]);
    exit;
}

// 2) Revisar caducidad
if (!empty($cup["fecha_caducidad"]) &&
    strtotime($cup["fecha_caducidad"]) < time()) {

    // marcar como caducado si aún no lo está
    if ($cup["estado"] !== "caducado") {
        $up = $conn->prepare("UPDATE cupones SET estado='caducado' WHERE id=?");
        $up->bind_param("i", $cup["id"]);
        $up->execute();
    }

    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón CADUCADO."]);
    exit;
}

// 3) Revisar si ya fue usado
if ($cup["estado"] === "usado") {
    echo json_encode(["status" => "ERROR", "mensaje" => "Este cupón YA fue validado."]);
    exit;
}

// 4) Marcar como usado
$up = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
$up->bind_param("i", $cup["id"]);
$up->execute();

// 5) Registrar validación
$reg = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
    VALUES (?, ?, NOW(), 'QR')
");
$reg->bind_param("ii", $cup["id"], $cup["comercio_id"]);
$reg->execute();

echo json_encode([
    "status" => "OK",
    "mensaje" => "Cupón validado correctamente en: " . $cup["comercio_nombre"]
]);
exit;
