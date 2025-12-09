<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ======================================
// MÉTRICAS GENERALES
// ======================================
$cupones_totales   = $conn->query("SELECT COUNT(*) AS t FROM cupones")->fetch_assoc()["t"];
$cupones_activos   = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='activo'")->fetch_assoc()["t"];
$cupones_usados    = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='usado'")->fetch_assoc()["t"];
$cupones_caducados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='caducado'")->fetch_assoc()["t"];
$validaciones      = $conn->query("SELECT COUNT(*) AS t FROM validaciones")->fetch_assoc()["t"];

// ======================================
// CUPONES POR COMERCIO
// ======================================
$por_comercio = $conn->query("
    SELECT com.nombre AS comercio, COUNT(c.id) AS total
    FROM cupones c
    LEFT JOIN comercios com ON com.id = c.comercio_id
    GROUP BY com.id
    ORDER BY total DESC
");

// ======================================
// USUARIOS REGISTRADOS POR MES
// ======================================
$usuarios_mes = $conn->query("
    SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, COUNT(*) AS total
    FROM usuarios
    GROUP BY mes
    ORDER BY mes DESC
");

// ======================================
// ÚLTIMAS VALIDACIONES
// ======================================
$ultimas_validaciones = $conn->query("
    SELECT v.fecha_validacion, 
        c.titulo, c.codigo,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre
    FROM validaciones v
    LEFT JOIN cupones c ON c.id = v.cupon_id
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    ORDER BY v.fecha_validacion DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}
.card-stat {
    padding: 22px;
    color: white;
    border-radius: 14px;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.card-blue { background: #3498db; }
.card-green { background: #1abc9c; }
.card-gray  { background: #7f8c8d; }
.card-red   { background: #c0392b; }
.card-purple { background: #9b59b6; }
.card-stat span {
    font-size: 32px;
    display: block;
    margin-top: 8px;
}
</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php" class="active">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Reportes y Estadísticas</h1>

    <!-- TARJETAS ESTADÍSTICAS -->
    <div class="stats-grid">
        <div class="card-stat card-blue">
            Cupones Totales
            <span><?= $cupones_totales ?></span>
        </div>

        <div class="card-stat card-green">
            Activos
            <span><?= $cupones_activos ?></span>
        </div>

        <div class="card-stat card-gray">
            Usados
            <span><?= $cupones_usados ?></span>
        </div>

        <div class="card-stat card-red">
            Caducados
            <span><?= $cupones_caducados ?></span>
        </div>

        <div class="card-stat card-purple">
            Validaciones Totales
            <span><?= $validaciones ?></span>
        </div>
    </div>

    <!-- CUPONES POR COMERCIO -->
    <div class="card">
        <h2>Cupones por Comercio</h2>

        <table>
            <tr>
                <th>Comercio</th>
                <th>Total de Cupones</th>
            </tr>

            <?php while ($row = $por_comercio->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["comercio"] ?: "—") ?></td>
                <td><?= $row["total"] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- USUARIOS REGISTRADOS POR MES -->
    <div class="card">
        <h2>Usuarios Registrados por Mes</h2>

        <table>
            <tr>
                <th>Mes</th>
                <th>Total</th>
            </tr>

            <?php while ($row = $usuarios_mes->fetch_assoc()): ?>
            <tr>
                <td><?= $row["mes"] ?></td>
                <td><?= $row["total"] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- ÚLTIMAS VALIDACIONES -->
    <div class="card">
        <h2>Últimas Validaciones</h2>

        <table>
            <tr>
                <th>Fecha</th>
                <th>Cupón</th>
                <th>Usuario</th>
                <th>Comercio</th>
            </tr>

            <?php while ($v = $ultimas_validaciones->fetch_assoc()): ?>
            <tr>
                <td><?= date("d/m/Y H:i", strtotime($v["fecha_validacion"])) ?></td>
                <td><?= htmlspecialchars($v["titulo"]) ?><br><small><?= $v["codigo"] ?></small></td>
                <td><?= htmlspecialchars($v["usuario_nombre"] ?: "—") ?></td>
                <td><?= htmlspecialchars($v["comercio_nombre"] ?: "—") ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>
