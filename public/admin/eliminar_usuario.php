<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar ID de usuario
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$user_id = intval($_GET["id"]);

// ==============================
// 1. OBTENER TODOS LOS CUPONES DEL USUARIO
// ==============================
$cupones = $conn->prepare("SELECT id FROM cupones WHERE usuario_id = ?");
$cupones->bind_param("i", $user_id);
$cupones->execute();
$res_cupones = $cupones->get_result();

// ==============================
// 2. ELIMINAR CASILLAS Y VALIDACIONES DE CADA CUPÓN
// ==============================
while ($cup = $res_cupones->fetch_assoc()) {

    $cup_id = $cup["id"];

    // Eliminar casillas
    $delCasillas = $conn->prepare("DELETE FROM cupon_casillas WHERE cupon_id = ?");
    $delCasillas->bind_param("i", $cup_id);
    $delCasillas->execute();

    // Eliminar validaciones
    $delValid = $conn->prepare("DELETE FROM validaciones WHERE cupon_id = ?");
    $delValid->bind_param("i", $cup_id);
    $delValid->execute();
}

// ==============================
// 3. ELIMINAR CUPONES DEL USUARIO
// ==============================
$delCupones = $conn->prepare("DELETE FROM cupones WHERE usuario_id = ?");
$delCupones->bind_param("i", $user_id);
$delCupones->execute();

// ==============================
// 4. ELIMINAR EL USUARIO
// ==============================
$delUser = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$delUser->bind_param("i", $user_id);

if ($delUser->execute()) {
    header("Location: usuarios.php?deleted=1");
    exit;
} else {
    die("Error eliminando usuario.");
}
