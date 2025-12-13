<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$comercios = $conn->query("SELECT * FROM comercios ORDER BY id DESC");

include "_header.php";
?>

<h1>Comercios</h1>

<div class="card">
    <a href="nuevo_comercio.php" class="btn-success" style="padding:10px 15px; display:inline-block; margin-bottom:15px;">
        ➕ Nuevo Comercio
    </a>

    <table>
        <tr>
            <th>ID</th>
            <th>Logo</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>

        <?php while ($c = $comercios->fetch_assoc()): ?>
        <tr>
            <td><?= $c["id"] ?></td>

            <td>
                <?php if (!empty($c["logo"])): ?>
                    <img src="/file.php?type=comercio&file=<?= basename($c["logo"]) ?>"
                         width="50" height="50" style="object-fit:cover;border-radius:8px;">
                <?php else: ?>
                    <span style="color:#aaa;">Sin logo</span>
                <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($c["nombre"]) ?></td>
            <td><?= htmlspecialchars($c["telefono"] ?: "—") ?></td>

            <td>
                <a href="ver_comercio.php?id=<?= $c["id"] ?>">👁 Ver</a> |
                <a href="editar_comercio.php?id=<?= $c["id"] ?>">✏ Editar</a> |
                <a href="eliminar_comercio.php?id=<?= $c["id"] ?>"
                   onclick="return confirm('¿Seguro que quieres eliminar este comercio?');">🗑 Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

<?php include "_footer.php"; ?>
