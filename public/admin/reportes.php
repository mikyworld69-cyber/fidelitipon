<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ================================
// ESTADÍSTICAS PRINCIPALES
// ================================
$totalCupones = $conn->query("SELECT COUNT(*) AS total FROM cupones")->fetch_assoc()["total"];
$cuponesActivos = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='activo'")->fetch_assoc()["total"];
$cuponesUsados = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='usado'")->fetch_assoc()["total"];
$cuponesCaducados = $conn->query("SELECT COUNT(*) AS total FROM cupones WHERE estado='caducado'")->fetch_assoc()["total"];

$totalUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()["total"];
$totalComercios = $conn->query("SELECT COUNT(*) AS total FROM comercios")->fetch_assoc()["total"];

$validacionesHoy = $conn->query("
    SELECT COUNT(*) AS total 
    FROM validaciones 
    WHERE DATE(fecha_validacion) = CURDATE()
")->fetch_assoc()["total"];

// Validaciones recientes
$ultimasValidaciones = $conn->query("
    SELECT 
        v.fecha_validacion,
        v.metodo,
        c.codigo,
        com.nombre AS comercio
    FROM validaciones v
    LEFT JOIN cupones c ON v.cupon_id = c.id
    LEFT JOIN comercios com ON v.comercio_id = com.id
    ORDER BY v.fecha_validacion DESC
    LIMIT 10
");

// Validaciones por comercio
$porComercio = $conn->query("
    SELECT 
        com.nombre AS comercio,
        COUNT(v.id) AS total
    FROM validaciones v
    LEFT JOIN comercios com ON v.comercio_id = com.id
    GROUP BY v.comercio_id
    ORDER BY total DESC
");

// Cupones creados por día (7 días)
$cuponesPorDia = $conn->query("
    SELECT DATE(fecha_creacion) AS dia, COUNT(*) AS total
    FROM cupones
    GROUP BY DATE(fecha_creacion)
    ORDER BY dia DESC
    LIMIT 7
");

// Nuevos usuarios por día (7 días)
$usuariosPorDia = $conn->query("
    SELECT DATE(fecha_registro) AS dia, COUNT(*) AS total
    FROM usuarios
    GROUP BY DATE(fecha_registro)
    ORDER BY dia DESC
    LIMIT 7
");

include "_header.php";
?>

<h1>Reportes del Sistema</h1>

<div class="card">
    <h2>Resumen General</h2>

    <p><strong>Total cupones:</strong> <?= $totalCupones ?></p>
    <p style="color:#27ae60;"><strong>Activos:</strong> <?= $cuponesActivos ?></p>
    <p style="color:#7f8c8d;"><strong>Usados:</strong> <?= $cuponesUsados ?></p>
    <p style="color:#c0392b;"><strong>Caducados:</strong> <?= $cuponesCaducados ?></p>

    <hr style="margin: 20px 0;">

    <p><strong>Total usuarios:</strong> <?= $totalUsuarios ?></p>
    <p><strong>Total comercios:</strong> <?= $totalComercios ?></p>

    <p><strong>Validaciones hoy:</strong> <?= $validacionesHoy ?></p>
</div>

<!-- VALIDACIONES RECIENTES -->
<div class="card">
    <h2>Últimas Validaciones</h2>

    <table>
        <tr>
            <th>Fecha</th>
            <th>Cupón</th>
            <th>Comercio</th>
            <th>Método</th>
        </tr>

        <?php while ($v = $ultimasValidaciones->fetch_assoc()): ?>
        <tr>
            <td><?= $v["fecha_validacion"] ?></td>
            <td><?= $v["codigo"] ?></td>
            <td><?= $v["comercio"] ?></td>
            <td><?= $v["metodo"] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- VALIDACIONES POR COMERCIO -->
<div class="card">
    <h2>Validaciones por Comercio</h2>

    <table>
        <tr>
            <th>Comercio</th>
            <th>Total validaciones</th>
        </tr>

        <?php while ($p = $porComercio->fetch_assoc()): ?>
        <tr>
            <td><?= $p["comercio"] ?></td>
            <td><?= $p["total"] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- CUPONES POR DÍA -->
<div class="card">
    <h2>Cupones creados por día (últimos 7 días)</h2>

    <table>
        <tr>
            <th>Día</th>
            <th>Total</th>
        </tr>

        <?php while ($c = $cuponesPorDia->fetch_assoc()): ?>
        <tr>
            <td><?= $c["dia"] ?></td>
            <td><?= $c["total"] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- USUARIOS POR DÍA -->
<div class="card">
    <h2>Usuarios nuevos por día (últimos 7 días)</h2>

    <table>
        <tr>
            <th>Día</th>
            <th>Total</th>
        </tr>

        <?php while ($u = $usuariosPorDia->fetch_assoc()): ?>
        <tr>
            <td><?= $u["dia"] ?></td>
            <td><?= $u["total"] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "_footer.php"; ?>
