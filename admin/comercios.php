<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// =====================================
// ELIMINAR COMERCIO
// =====================================
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    // OJO: si quieres evitar que borre cupones asociados, puedo hacer validación adicional.
    $del = $conn->prepare("DELETE FROM comercios WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: comercios.php");
    exit;
}

// =====================================
// LISTADO DE COMERCIOS
// =====================================
$sql = $conn->query("
    SELECT id, nombre, direccion, telefono, responsable, fecha_registro
    FROM comercios
    ORDER BY fecha_registro DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comercios | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php" class="active">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Comercios Registrados</h1>

    <div class="card">

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Responsable</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Fecha Registro</th>
                <th style="width:80px;">Acciones</th>
            </tr>

            <?php while ($c = $sql->fetch_assoc()): ?>
            <tr>
                <td><?= $c["id"] ?></td>
                <td><?= htmlspecialchars($c["nombre"]) ?></td>
                <td><?= htmlspecialchars($c["responsable"] ?: "—") ?></td>
                <td><?= htmlspecialchars($c["telefono"] ?: "—") ?></td>
                <td><?= htmlspecialchars($c["direccion"] ?: "—") ?></td>
                <td><?= date("d/m/Y", strtotime($c["fecha_registro"])) ?></td>

                <td>
                    <a class="btn-danger"
                       href="?eliminar=<?= $c['id'] ?>"
                       onclick="return confirm('¿Eliminar este comercio? Se eliminarán también sus cupones.')">
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
