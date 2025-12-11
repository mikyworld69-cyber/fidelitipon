<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si no está logueado → fuera
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* =======================
   CONSULTAS RESUMEN
======================= */

// Total usuarios
$totalUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];

// Total comercios
$totalComercios = $conn->query("SELECT COUNT(*) AS total FROM comercios")->fetch_assoc()['total'];

// Total cupones
$totalCupones = $conn->query("SELECT COUNT(*) AS total FROM cupones")->fetch_assoc()['total'];

// Últimos 5 usuarios
$ultimosUsuarios = $conn->query("
    SELECT nombre, telefono, fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC 
    LIMIT 5
");

// Últimos 5 cupones
$ultimosCupones = $conn->query("
    SELECT titulo, estado, fecha_caducidad 
    FROM cupones 
    ORDER BY id DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">
<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.10);
    text-align: center;
}

.stat-card h2 {
    font-size: 42px;
    margin: 10px 0;
    color: #3498db;
}

.stat-card p {
    font-size: 16px;
    color: #555;
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

    <h1>Panel de Control</h1>

    <div class="dashboard-cards">

        <div class="stat-card">
            <p>Usuarios registrados</p>
            <h2><?= $totalUsuarios ?></h2>
        </div>

        <div class="stat-card">
            <p>Comercios activos</p>
            <h2><?= $totalComercios ?></h2>
        </div>

        <div class="stat-card">
            <p>Cupones creados</p>
            <h2><?= $totalCupones ?></h2>
        </div>

    </div>

    <!-- Últimos usuarios -->
    <div class="card">
        <h3>🧍 Últimos usuarios registrados</h3>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Fecha</th>
            </tr>

            <?php while ($u = $ultimosUsuarios->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($u["nombre"] ?: "—") ?></td>
                <td><?= htmlspecialchars($u["telefono"]) ?></td>
                <td><?= date("d/m/Y", strtotime($u["fecha_registro"])) ?></td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>


    <!-- Últimos cupones -->
    <div class="card">
        <h3>🎟 Últimos cupones generados</h3>
        <table>
            <tr>
                <th>Título</th>
                <th>Estado</th>
                <th>Caducidad</th>
            </tr>

            <?php while ($c = $ultimosCupones->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c["titulo"]) ?></td>
                <td><?= strtoupper($c["estado"]) ?></td>
                <td><?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>

</div><!-- content -->

</body>
</html>
