<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin | Fidelitipon</title>
<link rel="stylesheet" href="admin.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">📊 Dashboard</a>
    <a href="usuarios.php" class="<?= basename($_SERVER['PHP_SELF'])=='usuarios.php'?'active':'' ?>">👤 Usuarios</a>
    <a href="comercios.php" class="<?= basename($_SERVER['PHP_SELF'])=='comercios.php'?'active':'' ?>">🏪 Comercios</a>
    <a href="cupones.php" class="<?= basename($_SERVER['PHP_SELF'])=='cupones.php'?'active':'' ?>">🎟 Cupones</a>
    <a href="validar.php" class="<?= basename($_SERVER['PHP_SELF'])=='validar.php'?'active':'' ?>">📷 Validar</a>
    <a href="reportes.php" class="<?= basename($_SERVER['PHP_SELF'])=='reportes.php'?'active':'' ?>">📈 Reportes</a>
    <a href="notificaciones.php" class="<?= basename($_SERVER['PHP_SELF'])=='notificaciones.php'?'active':'' ?>">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">
