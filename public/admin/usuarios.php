<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ELIMINAR USUARIO
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    $del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: usuarios.php");
    exit;
}

// LISTAR USUARIOS
$sql = $conn->query("
    SELECT id, nombre, telefono, fecha_registro
    FROM usuarios
    ORDER BY fecha_registro DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php" class="active">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Usuarios Registrados</h1>

    <div class="card">

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Fecha Registro</th>
                <th style="width:80px;">Acciones</th>
            </tr>

            <?php while ($u = $sql->fetch_assoc()): ?>
            <tr>
                <td><?= $u["id"] ?></td>
                <td><?= htmlspecialchars($u["nombre"] ?: "—") ?></td>
                <td><?= htmlspecialchars($u["telefono"]) ?></td>
                <td><?= date("d/m/Y", strtotime($u["fecha_registro"])) ?></td>

                <td>
                    <a class="btn-danger" 
                       href="?eliminar=<?= $u['id'] ?>" 
                       onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
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
