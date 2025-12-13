<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$usuarios = $conn->query("
    SELECT id, nombre, telefono, fecha_registro
    FROM usuarios
    ORDER BY id DESC
");

include "_header.php";
?>

<h1>Usuarios</h1>

<div class="card">

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Registrado</th>
            <th>Acciones</th>
        </tr>

        <?php while ($u = $usuarios->fetch_assoc()): ?>
        <tr>
            <td><?= $u["id"] ?></td>
            <td><?= htmlspecialchars($u["nombre"]) ?></td>
            <td><?= htmlspecialchars($u["telefono"]) ?></td>
            <td><?= date("d/m/Y", strtotime($u["fecha_registro"])) ?></td>
            <td>
                <a href="ver_usuario.php?id=<?= $u["id"] ?>">👁 Ver</a> |
                <a href="editar_usuario.php?id=<?= $u["id"] ?>">✏ Editar</a> |
                <a href="eliminar_usuario.php?id=<?= $u["id"] ?>"
                   onclick="return confirm('¿Eliminar usuario y todos sus cupones?');">🗑 Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include "_footer.php"; ?>
