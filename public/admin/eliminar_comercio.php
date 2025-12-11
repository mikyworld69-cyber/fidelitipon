<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar que llega el ID
if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$comercio_id = intval($_GET["id"]);

// ==========================================
// 1) OBTENER TODOS LOS CUPONES DEL COMERCIO
// ==========================================
$q = $conn->prepare("
    SELECT id
    FROM cupones
    WHERE comercio_id = ?
");
$q->bind_param("i", $comercio_id);
$q->execute();
$cupones = $q->get_result();

// ==========================================
// 2) ELIMINAR VALIDACIONES Y CASILLAS
// ==========================================
while ($cup = $cupones->fetch_assoc()) {

    $cup_id = $cup["id"];

    // Eliminar validaciones del cupón
    $delVal = $conn->prepare("DELETE FROM validaciones WHERE cupon_id = ?");
    $delVal->bind_param("i", $cup_id);
    $delVal->execute();

    // Eliminar casillas del cupón
    $delCas = $conn->prepare("DELETE FROM cupon_casillas WHERE cupon_id = ?");
    $delCas->bind_param("i", $cup_id);
    $delCas->execute();
}

// ==========================================
// 3) ELIMINAR CUPONES DEL COMERCIO
// ==========================================
$delCupones = $conn->prepare("DELETE FROM cupones WHERE comercio_id = ?");
$delCupones->bind_param("i", $comercio_id);
$delCupones->execute();

// ==========================================
// 4) ELIMINAR EL COMERCIO
// ==========================================
$delCom = $conn->prepare("DELETE FROM comercios WHERE id = ?");
$delCom->bind_param("i", $comercio_id);

if ($delCom->execute()) {
    header("Location: comercios.php?msg=comercio_deleted");
    exit;
} else {
    echo "❌ Error al eliminar el comercio.";
}
?>
