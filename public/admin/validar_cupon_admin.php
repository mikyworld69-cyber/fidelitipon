<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No autorizado."]);
    exit;
}

if (!isset($_GET["codigo"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No se recibió ningún código."]);
    exit;
}

$codigo = trim($_GET["codigo"]);

// ================================
// 1. Buscar cupón por código
// ================================
$sql = $conn->prepare("
    SELECT 
        c.id,
        c.codigo,
        c.estado,
        c.fecha_caducidad,
        c.comercio_id,
        com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.codigo = ?
    LIMIT 1
");
$sql->bind_param("s", $codigo);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón no encontrado."]);
    exit;
}

$cupon = $res->fetch_assoc();

// ================================
// 2. Validar estado del cupón
// ================================

// Si caducó
if (!empty($cupon["fecha_caducidad"]) && strtotime($cupon["fecha_caducidad"]) < time()) {
    echo json_encode(["status" => "CADUCADO", "mensaje" => "Cupón caducado."]);
    exit;
}

// Si ya se usó
if ($cupon["estado"] === "usado") {
    echo json_encode(["status" => "ERROR", "mensaje" => "Este cupón ya fue validado anteriormente."]);
    exit;
}

// ================================
// 3. Marcar cupón como usado
// ================================
$update = $conn->prepare("UPDATE cupones SET estado = 'usado' WHERE id = ?");
$update->bind_param("i", $cupon["id"]);
$update->execute();

// ================================
// 4. Registrar validación
// ================================
$now = date("Y-m-d H:i:s");
$metodo = "QR";

$insertVal = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
    VALUES (?, ?, ?, ?)
");

$insertVal->bind_param("iiss",
    $cupon["id"],
    $cupon["comercio_id"],
    $now,
    $metodo
);

$insertVal->execute();

// ================================
// 5. Respuesta exitosa
// ================================
echo json_encode([
    "status" => "OK",
    "mensaje" => "Cupón validado correctamente en el comercio: " . $cupon["comercio_nombre"]
]);
exit;
