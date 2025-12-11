<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar parámetro
if (!isset($_GET["id"])) {
    header("Location: cupones.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// ================================
// ELIMINAR VALIDACIONES ASOCIADAS
// ================================
$deleteVal = $conn->prepare("DELETE FROM validaciones WHERE cupon_id = ?");
$deleteVal->bind_param("i", $cup_id);
$deleteVal->execute();

// ================================
// ELIMINAR CASILLAS DEL CUPÓN
// ================================
$deleteCas = $conn->prepare("DELETE FROM cupon_casillas WHERE cupon_id = ?");
$deleteCas->bind_param("i", $cup_id);
$deleteCas->execute();

// ================================
// ELIMINAR EL CUPÓN
// ================================
$deleteCup = $conn->prepare("DELETE FROM cupones WHERE id = ?");
$deleteCup->bind_param("i", $cup_id);

if ($deleteCup->execute()) {
    header("Location: cupones.php?msg=deleted");
    exit;
} else {
    echo "Error al eliminar el cupón.";
}
?>
