<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// VALIDAR ADMIN
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// VALIDAR ID DEL CUPÓN
if (!isset($_GET["id"])) {
    die("Cupón no especificado.");
}

$cup_id = intval($_GET["id"]);

// ----------------------------
// ELIMINAR CASILLAS DEL CUPÓN
// ----------------------------
$delCas = $conn->prepare("DELETE FROM cupon_casillas WHERE cupon_id = ?");
$delCas->bind_param("i", $cup_id);
$delCas->execute();

// ----------------------------
// ELIMINAR VALIDACIONES
// ----------------------------
$delVal = $conn->prepare("DELETE FROM validaciones WHERE cupon_id = ?");
$delVal->bind_param("i", $cup_id);
$delVal->execute();

// ----------------------------
// ELIMINAR CUPÓN
// ----------------------------
$delCup = $conn->prepare("DELETE FROM cupones WHERE id = ?");
$delCup->bind_param("i", $cup_id);

if ($delCup->execute()) {
    header("Location: cupones.php?deleted=1");
    exit;
} else {
    die("Error eliminando el cupón.");
}
