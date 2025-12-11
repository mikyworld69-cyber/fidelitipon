<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar parámetro
if (!isset($_GET["id"])) {
    header("Location: usuarios.php");
    exit;
}

$user_id = intval($_GET["id"]);

// =====================================================
// 1. OBTENER TODOS LOS CUPONES DEL USUARIO
// =====================================================
$q = $conn->prepare("
    SELECT id 
    FROM cupones 
    WHERE usuario_id = ?
");
$q->bind_param("i", $user_id);
$q->execute();
$cupones = $q->get_result();

// =====================================================
// 2. ELIMINAR CASILLAS Y VALIDACIONES DE CADA CUPÓN
// =====================================================
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

// =====================================================
// 3. ELIMINAR LOS CUPONES DEL USUARIO
// =====================================================
$delCupones = $conn->prepare("DELETE FROM cupones WHERE usuario_id = ?");
$delCupones->bind_param("i", $user_id);
$delCupones->execute();

// =====================================================
// 4. ELIMINAR EL USUARIO
// =====================================================
$delUser = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$delUser->bind_param("i", $user_id);

if ($delUser->execute()) {
    header("Location: usuarios.php?msg=user_deleted");
    exit;
} else {
    echo "Error al eliminar el usuario.";
}
?>
