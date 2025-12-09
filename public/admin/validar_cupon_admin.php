<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "error", "msg" => "No autorizado"]);
    exit;
}

if (!isset($_POST["codigo"])) {
    echo json_encode(["status" => "error", "msg" => "Código no recibido"]);
    exit;
}

$codigo = trim($_POST["codigo"]);

// Buscar cupón
$sql = $conn->prepare("SELECT * FROM cupones WHERE codigo = ?");
$sql->bind_param("s", $codigo);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status" => "error", "msg" => "Cupón no encontrado"]);
    exit;
}

$cupon = $res->fetch_assoc();

// Estado
if ($cupon["estado"] === "usado") {
    echo json_encode(["status" => "error", "msg" => "Cupón ya usado"]);
    exit;
}

if ($cupon["estado"] === "caducado") {
    echo json_encode(["status" => "error", "msg" => "Cupón caducado"]);
    exit;
}

// Caducidad por fecha
$hoy = date("Y-m-d");
if ($cupon["fecha_caducidad"] < $hoy) {

    // marcar como caducado
    $up = $conn->prepare("UPDATE cupones SET estado = 'caducado' WHERE id = ?");
    $up->bind_param("i", $cupon["id"]);
    $up->execute();

    echo json_encode(["status" => "error", "msg" => "El cupón ha caducado"]);
    exit;
}

// Marcar cupón como usado
$up = $conn->prepare("UPDATE cupones SET estado = 'usado' WHERE id = ?");
$up->bind_param("i", $cupon["id"]);
$up->execute();

// Registrar validación
$log = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, metodo)
    VALUES (?, ?, 'admin')
");
$log->bind_param("ii", $cupon["id"], $cupon["comercio_id"]);
$log->execute();

echo json_encode([
    "status" => "ok",
    "titulo" => $cupon["titulo"],
    "descripcion" => $cupon["descripcion"],
    "msg" => "Cupón validado correctamente."
]);
exit;
?>
