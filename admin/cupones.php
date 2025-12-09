<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ==============================
// ELIMINAR CUPÓN
// ==============================
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    $del = $conn->prepare("DELETE FROM cupones WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: cupones.php");
    exit;
}

// ==============================
// LISTADO DE CUPONES
// ==============================
$sql = $conn->query("
    SELECT 
        c.id,
        c.titulo,
        c.estado,
        c.fecha_creacion,
        c.fecha_caducidad,
        c.codigo,
        u.nombre AS usuario_nombre,
        u.telefono AS usuario_telefono,
        com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    ORDER BY c.fecha_creacion DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cupones | Fidelitipon Admin</title>

<link rel="stylesheet" href="admin.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php" class="active">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Gestión de Cupones</h1>

    <a href="cupon_crear.php" class="btn btn-success" style="margin-bottom:20px;">
        ➕ Crear Cupón
    </a>

    <div class="card">

        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Comercio</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Creación</th>
                <th>Caducidad</th>
                <th>Código</th>
                <th style="width:80px;">Acciones</th>
            </tr>

            <?php while ($c = $sql->fetch_assoc()): ?>

            <?php
            // BADGE DE ESTADO
            $badgeClass = "badge-activo";
            if ($c["estado"] === "usado") $badgeClass = "badge-usado";
            if ($c["estado"] === "caducado") $badgeClass = "badge-caducado";
            ?>

            <tr>
                <td><?= $c["id"] ?></td>
                <td><?= htmlspecialchars($c["titulo"]) ?></td>

                <td><?= htmlspecialchars($c["comercio_nombre"] ?: "—") ?></td>

                <td>
                    <?= htmlspecialchars($c["usuario_nombre"] ?: "—") ?>
                    <br>
                    <span style="font-size:13px; color:#555;">
                        <?= htmlspecialchars($c["usuario_telefono"] ?: "") ?>
                    </span>
                </td>

                <td>
                    <span class="badge <?= $badgeClass ?>">
                        <?= strtoupper($c["estado"]) ?>
                    </span>
                </td>

                <td><?= date("d/m/Y", strtotime($c["fecha_creacion"])) ?></td>

                <td><?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></td>

                <td><?= htmlspecialchars($c["codigo"]) ?></td>

                <td>
                    <a class="btn-danger"
                       href="?eliminar=<?= $c['id'] ?>"
                       onclick="return confirm('¿Eliminar este cupón?')">
                        🗑
                    </a>
                </td>
            </tr>

            <?php endwhile; ?>

        </table>

    </div>

</div>

</body>
</html>
