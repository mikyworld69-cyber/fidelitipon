<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Verificar login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ---------- CONSULTAS ----------
$totUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()["total"];
$totComercios = $conn->query("SELECT COUNT(*) AS total FROM comercios")->fetch_assoc()["total"];
$totCupones = $conn->query("SELECT COUNT(*) AS total FROM cupones")->fetch_assoc()["total"];
$cuponesPend = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='pendiente'")->fetch_assoc()["total"];
$cuponesCanj = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='canjeado'")->fetch_assoc()["total"];
$cuponesCad = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='caducado'")->fetch_assoc()["total"];
$totValidaciones = $conn->query("SELECT COUNT(*) AS total FROM validaciones")->fetch_assoc()["total"];
$totNotificaciones = $conn->query("SELECT COUNT(*) AS total FROM notificaciones")->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin | Fidelitipon</title>

<style>
body {
    background: #f4f6f9;
    font-family: Arial;
    margin: 0;
}
header {
    background: #3498db;
    padding: 15px;
    color: white;
    font-size: 22px;
}

.container {
    padding: 20px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    margin-bottom: 20px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 20px;
}

.card h3 {
    margin: 0 0 10px;
    color: #3498db;
}

.value {
    font-size: 28px;
    font-weight: bold;
}
</style>

</head>
<body>

<header>
    Panel Administrador – Fidelitipon
</header>

<div class="container">

    <div class="grid">
        
        <div class="card">
            <h3>Usuarios Registrados</h3>
            <div class="value"><?= $totUsuarios ?></div>
        </div>

        <div class="card">
            <h3>Comercios</h3>
            <div class="value"><?= $totComercios ?></div>
        </div>

        <div class="card">
            <h3>Total Cupones</h3>
            <div class="value"><?= $totCupones ?></div>
        </div>

        <div class="card">
            <h3>Cupones Pendientes</h3>
            <div class="value"><?= $cuponesPend ?></div>
        </div>

        <div class="card">
            <h3>Cupones Canjeados</h3>
            <div class="value"><?= $cuponesCanj ?></div>
        </div>

        <div class="card">
            <h3>Cupones Caducados</h3>
            <div class="value"><?= $cuponesCad ?></div>
        </div>

        <div class="card">
            <h3>Validaciones Realizadas</h3>
            <div class="value"><?= $totValidaciones ?></div>
        </div>

        <div class="card">
            <h3>Notificaciones Enviadas</h3>
            <div class="value"><?= $totNotificaciones ?></div>
        </div>

    </div>

</div>

</body>
</html>
