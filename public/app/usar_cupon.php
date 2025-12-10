<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Validar cupón recibido
if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cupon_id = intval($_GET["id"]);

// Buscar cupón
$sql = $conn->prepare("
    SELECT id, estado, fecha_caducidad
    FROM cupones
    WHERE id = ? AND usuario_id = ?
    LIMIT 1
");
$sql->bind_param("i", $cupon_id, $user_id);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    header("Location: panel_usuario.php?msg=notfound");
    exit;
}

$cupon = $res->fetch_assoc();

// Validaciones
if ($cupon["estado"] !== "activo") {
    header("Location: ver_cupon.php?id=$cupon_id&msg=noactivo");
    exit;
}

// Ver si está caducado
$hoy = date("Y-m-d");
if ($cupon["fecha_caducidad"] < $hoy) {
    // Marcar caducado si no lo está
    $up = $conn->prepare("UPDATE cupones SET estado='caducado' WHERE id=?");
    $up->bind_param("i", $cupon_id);
    $up->execute();

    header("Location: ver_cupon.php?id=$cupon_id&msg=caducado");
    exit;
}

// Marcar como usado
$upd = $conn->prepare("
    UPDATE cupones 
    SET estado = 'usado', fecha_uso = NOW()
    WHERE id = ?
");
$upd->bind_param("i", $cupon_id);
$upd->execute();

header("Location: ver_cupon.php?id=$cupon_id&msg=ok");
exit;
