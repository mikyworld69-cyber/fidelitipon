<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ========================
// MÉTRICAS DEL DASHBOARD
// ========================

// Total usuarios
$total_usuarios = $conn->query("SELECT COUNT(*) AS t FROM usuarios")->fetch_assoc()["t"];

// Total comercios
$total_comercios = $conn->query("SELECT COUNT(*) AS t FROM comercios")->fetch_assoc()["t"];

// Total cupones
$total_cupones = $conn->query("SELECT COUNT(*) AS t FROM cupones")->fetch_assoc()["t"];

// Activos
$activos = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='activo'")->fetch_assoc()["t"];

// Usados
$usados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='usado'")->fetch_assoc()["t"];

// Caducados
$caducados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='caducado'")->fetch_assoc()["t"];

// Validaciones totales
$validaciones = $conn->query("SELECT COUNT(*) AS t FROM validaciones")->fetch_assoc()["t"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.card-stat {
    padding: 25px;
    color: white;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-size: 18px;
}

.card-blue { background: #3498db; }
.card-green { background: #1abc9c; }
.card-orange { background: #e67e22; }
.card-purple { background: #9b59b6; }
.card-gray  { background: #7f8c8d; }
.card-red   { background: #c0392b; }

.card-stat span {
    font-size: 34px;
    font-weight: bold;
    display: block;
    margin-top: 10px;
}
</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php" class="active">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Dashboard</h1>
    <p>Bienvenido al panel administrativo de Fidelitipon. Aquí tienes una visión general.</p>

    <div class="dashboard-grid">

        <div class="card-stat card-blue">
            Usuarios registrados
            <span><?= $total_usuarios ?></span>
        </div>

        <div class="card-stat card-green">
            Comercios
            <span><?= $total_comercios ?></span>
        </div>

        <div class="card-stat card-orange">
            Cupones totales
            <span><?= $total_cupones ?></span>
        </div>

        <div class="card-stat card-green">
            Cupones activos
            <span><?= $activos ?></span>
        </div>

        <div class="card-stat card-gray">
            Cupones usados
            <span><?= $usados ?></span>
        </div>

        <div class="card-stat card-red">
            Cupones caducados
            <span><?= $caducados ?></span>
        </div>

        <div class="card-stat card-purple">
            Validaciones totales
            <span><?= $validaciones ?></span>
        </div>

    </div>

</div>

</body>
</html>
