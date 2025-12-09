<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . "/../config/db.php";

// Recibir código
if (!isset($_POST["codigo"])) {
    echo json_encode(["status" => "error", "msg" => "Código no recibido"]);
    exit;
}

$codigo = trim($_POST["codigo"]);
$usuario_id = $_SESSION["usuario_id"] ?? null;

// Seguridad básica
if (!$usuario_id) {
    echo json_encode(["status" => "error", "msg" => "Usuario no autenticado"]);
    exit;
}

// Buscar cupón
$sql = $conn->prepare("SELECT * FROM cupones WHERE codigo = ? AND usuario_id = ?");
$sql->bind_param("si", $codigo, $usuario_id);
$sql->execute();
$res = $sql->get_result();

// Si no existe
if ($res->num_rows == 0) {
    echo json_encode(["status" => "error", "msg" => "Cupón no encontrado o no pertenece al usuario"]);
    exit;
}

$cupon = $res->fetch_assoc();

// Verificar estado
if ($cupon["estado"] === "usado") {
    echo json_encode(["status" => "error", "msg" => "El cupón ya ha sido usado"]);
    exit;
}

if ($cupon["estado"] === "caducado") {
    echo json_encode(["status" => "error", "msg" => "El cupón está caducado"]);
    exit;
}

// Verificar caducidad por fecha
$hoy = date("Y-m-d");
if ($cupon["fecha_caducidad"] < $hoy) {

    // Actualizar en BD como caducado
    $up = $conn->prepare("UPDATE cupones SET estado = 'caducado' WHERE id = ?");
    $up->bind_param("i", $cupon["id"]);
    $up->execute();

    echo json_encode(["status" => "error", "msg" => "El cupón ha caducado"]);
    exit;
}

// Marcar como usado
$up2 = $conn->prepare("UPDATE cupones SET estado = 'usado' WHERE id = ?");
$up2->bind_param("i", $cupon["id"]);
$up2->execute();

// Registrar en tabla validaciones
$log = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, metodo) 
    VALUES (?, ?, 'qr')
");
$log->bind_param("ii", $cupon["id"], $cupon["comercio_id"]);
$log->execute();

// ÉXITO
echo json_encode([
    "status" => "ok",
    "titulo" => $cupon["titulo"],
    "descripcion" => $cupon["descripcion"],
    "msg" => "Cupón validado correctamente"
]);
exit;
?>
