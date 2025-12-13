<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// -------------------------
// Obtener datos globales
// -------------------------

$totalUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$totalComercios = $conn->query("SELECT COUNT(*) AS total FROM comercios")->fetch_assoc()['total'];
$totalCupones = $conn->query("SELECT COUNT(*) AS total FROM cupones")->fetch_assoc()['total'];
$totalValidaciones = $conn->query("SELECT COUNT(*) AS total FROM validaciones")->fetch_assoc()['total'];

// -------------------------
// Reporte de cupones completos
// -------------------------

$sqlCupones = $conn->query("
    SELECT 
        c.id,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_caducidad,
        c.codigo,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre,
        (
            SELECT COUNT(*) FROM cupon_casillas 
            WHERE cupon_id = c.id AND marcada = 1
        ) AS usadas,
        (
            SELECT COUNT(*) FROM cupon_casillas 
            WHERE cupon_id = c.id
        ) AS total_casillas
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    ORDER BY c.id DESC
");

// -------------------------

include "_header.php";
?>

<style>
.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.10);
    margin-bottom: 22px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}
th {
    background: #3498db;
    padding: 10px;
    color: white;
    font-size: 14px;
}
td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
.badge {
    padding: 4px 10px;
    border-radius: 8px;
    color: white;
    font-size: 12px;
}
.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }
</style>

<h1>📈 Reportes</h1>

<!-- Tarjetas resumen -->
<div class="card">
    <h3>Resumen General</h3>
    <p><strong>Usuarios:</strong> <?= $totalUsuarios ?></p>
    <p><strong>Comercios:</strong> <?= $totalComercios ?></p>
    <p><strong>Cupones generados:</strong> <?= $totalCupones ?></p>
    <p><strong>Validaciones registradas:</strong> <?= $totalValidaciones ?></p>
</div>

<!-- Reporte de cupones -->
<div class="card">
    <h3>🎟 Estado de Cupones</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th>Comercio</th>
            <th>Casillas</th>
            <th>Caducidad</th>
        </tr>

        <?php while ($c = $sqlCupones->fetch_assoc()): 

            $estado = strtolower($c["estado"]);
            $badgeClass =
                $estado === "activo"   ? "badge-activo" :
                ($estado === "usado"   ? "badge-usado" :
                "badge-caducado");
        ?>
        <tr>
            <td><?= $c["id"] ?></td>
            <td><?= htmlspecialchars($c["codigo"]) ?></td>
            <td><?= htmlspecialchars($c["titulo"]) ?></td>
            <td><span class="badge <?= $badgeClass ?>"><?= strtoupper($estado) ?></span></td>
            <td><?= htmlspecialchars($c["usuario_nombre"] ?: "—") ?></td>
            <td><?= htmlspecialchars($c["comercio_nombre"] ?: "—") ?></td>
            <td><?= $c["usadas"] ?>/<?= $c["total_casillas"] ?></td>

            <!-- Fecha con fix definitivo -->
            <td>
            <?php 
                if (!empty($c["fecha_caducidad"])) {
                    echo date("d/m/Y", strtotime($c["fecha_caducidad"]));
                } else {
                    echo "<span style='color:#888;'>Sin caducidad</span>";
                }
            ?>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

<?php include "_footer.php"; ?>
