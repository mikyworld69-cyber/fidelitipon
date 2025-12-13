<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

// Solo admin
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener todos los usuarios
$sql = $conn->query("SELECT id, nombre, telefono, fecha_registro FROM usuarios ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios | Fidelitipon</title>
<link rel="stylesheet" href="admin.css">

<style>
.btn-add {
    display:inline-block;
    padding:10px 18px;
    background:#27ae60;
    color:white;
    border-radius:8px;
    text-decoration:none;
    margin-bottom:20px;
    font-weight:bold;
}
.btn-add:hover {
    background:#1f8c4d;
}

table {
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:12px;
    overflow:hidden;
}

table th {
    background:#3498db;
    color:white;
    padding:12px;
    text-align:left;
}

table td {
    padding:12px;
    border-bottom:1px solid #ddd;
}

.actions a {
    margin-right:8px;
    text-decoration:none;
    padding:6px 10px;
    border-radius:6px;
    font-size:14px;
}

.btn-ver { background:#2980b9;color:white; }
.btn-editar { background:#27ae60;color:white; }
.btn-eliminar { background:#c0392b;color:white; }

.btn-ver:hover { background:#1f6690; }
.btn-editar:hover { background:#1f8c4d; }
.btn-eliminar:hover { background:#992d22; }

</style>

</head>
<body>

<div class="content">

    <h1>Usuarios</h1>

    <!-- Botón crear usuario -->
    <a href="nuevo_usuario.php" class="btn-add">+ Crear Usuario</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Registro</th>
            <th>Acciones</th>
        </tr>

        <?php while ($u = $sql->fetch_assoc()): ?>
        <tr>
            <td><?= $u["id"] ?></td>
            <td><?= htmlspecialchars($u["nombre"]) ?></td>
            <td><?= htmlspecialchars($u["telefono"]) ?></td>
            <td><?= date("d/m/Y", strtotime($u["fecha_registro"])) ?></td>

            <td class="actions">
                <a href="ver_usuario.php?id=<?= $u["id"] ?>" class="btn-ver">Ver</a>
                <a href="editar_usuario.php?id=<?= $u["id"] ?>" class="btn-editar">Editar</a>
                <a href="eliminar_usuario.php?id=<?= $u["id"] ?>" class="btn-eliminar"
                   onclick="return confirm('¿Eliminar usuario? Esto es irreversible.');">
                    Eliminar
                </a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include "_footer.php"; ?>

</body>
</html>
