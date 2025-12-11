<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener todos los usuarios
$usuarios = $conn->query("
    SELECT 
        u.id,
        u.nombre,
        u.telefono,
        u.fecha_registro,
        (SELECT COUNT(*) FROM cupones WHERE usuario_id = u.id) AS total_cupones
    FROM usuarios u
    ORDER BY u.id DESC
");
?>

<?php include "_header.php"; ?>

<h1>Usuarios</h1>

<?php if (isset($_GET["deleted"])): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        ✔ Usuario eliminado correctamente
    </div>
<?php endif; ?>

<div class="card">
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Fecha Registro</th>
        <th>Cupones</th>
        <th>Acciones</th>
    </tr>

    <?php while ($u = $usuarios->fetch_assoc()): ?>
    <tr>
        <td><?= $u["id"] ?></td>
        <td><?= htmlspecialchars($u["nombre"]) ?></td>
        <td><?= htmlspecialchars($u["telefono"]) ?></td>
        <td><?= date("d/m/Y H:i", strtotime($u["fecha_registro"])) ?></td>
        <td><?= $u["total_cupones"] ?></td>

        <td>
            <a class="btn btn-small" href="ver_usuario.php?id=<?= $u['id'] ?>">👁 Ver</a>
            <a class="btn btn-small" href="editar_usuario.php?id=<?= $u['id'] ?>">✏ Editar</a>
            <a class="btn btn-small btn-danger"
               href="eliminar_usuario.php?id=<?= $u['id'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar este usuario? Se borrarán sus cupones.');">
               ❌
            </a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>
</div>

<?php include "_footer.php"; ?>
