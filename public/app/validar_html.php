<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// ======================================
// 1. Validación parámetro
// ======================================
if (!isset($_GET["codigo"])) {
    $status = "error";
    $error = "No se recibió ningún código.";
    $logo = null;
    include "validar_template.php";
    exit;
}

$codigo = intval($_GET["codigo"]);

// ======================================
// 2. Buscar cupón
// ======================================
$sql = $conn->prepare("
    SELECT id, usuario_id, comercio_id, estado, fecha_caducidad, total_casillas
    FROM cupones
    WHERE id = ?
    LIMIT 1
");
$sql->bind_param("i", $codigo);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    $status = "error";
    $error = "Cupón no encontrado.";
    $logo = null;
    include "validar_template.php";
    exit;
}

// ======================================
// 3. Obtener comercio (logo + nombre)
// ======================================
$qCom = $conn->prepare("
    SELECT nombre, logo
    FROM comercios
    WHERE id = ?
");
$qCom->bind_param("i", $cup["comercio_id"]);
$qCom->execute();
$comercio = $qCom->get_result()->fetch_assoc();

$logo = (!empty($comercio["logo"]) ? $comercio["logo"] : "/img/default_logo.png");

// ======================================
// 4. Validar caducidad
// ======================================
if (!empty($cup["fecha_caducidad"]) && strtotime($cup["fecha_caducidad"]) < time()) {
    $status = "caducado";
    include "validar_template.php";
    exit;
}

// ======================================
// 5. Contar casillas usadas
// ======================================
$q1 = $conn->prepare("
    SELECT COUNT(*) AS usadas
    FROM cupon_casillas
    WHERE cupon_id = ? AND estado = 1
");
$q1->bind_param("i", $cup["id"]);
$q1->execute();
$usadas = $q1->get_result()->fetch_assoc()["usadas"];


// SI YA ESTABA COMPLETO
if ($usadas >= $cup["total_casillas"]) {
    $status = "completo";
    $casillaMarcada = null;
    $faltan = 0;

    // asegurar estado usado
    $end = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
    $end->bind_param("i", $cup["id"]);
    $end->execute();

    include "validar_template.php";
    exit;
}


// ======================================
// 6. Buscar primera casilla libre
// ======================================
$q2 = $conn->prepare("
    SELECT id, numero_casilla
    FROM cupon_casillas
    WHERE cupon_id = ? AND estado = 0
    ORDER BY numero_casilla ASC
    LIMIT 1
");
$q2->bind_param("i", $cup["id"]);
$q2->execute();
$casilla = $q2->get_result()->fetch_assoc();

if (!$casilla) {
    $status = "completo";
    $faltan = 0;
    include "validar_template.php";
    exit;
}


// ======================================
// 7. Marcar casilla
// ======================================
$now = date("Y-m-d H:i:s");

$upd = $conn->prepare("
    UPDATE cupon_casillas
    SET estado = 1, fecha_marcado = ?
    WHERE id = ?
");
$upd->bind_param("si", $now, $casilla["id"]);
$upd->execute();


// Registrar validación
$reg = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
    VALUES (?, ?, ?, 'QR')
");
$reg->bind_param("iis", $cup["id"], $cup["comercio_id"], $now);
$reg->execute();


// ======================================
// 8. Cálculo final
// ======================================
$nuevasUsadas = $usadas + 1;
$faltan = $cup["total_casillas"] - $nuevasUsadas;

$casillaMarcada = $casilla["numero_casilla"];

// Si se completó el cupón
if ($faltan == 0) {
    $status = "completado";

    $end = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
    $end->bind_param("i", $cup["id"]);
    $end->execute();
} else {
    $status = "ok";
}


// ======================================
// 9. Mostrar plantilla visual
// ======================================
include "validar_template.php";
exit;

?>
