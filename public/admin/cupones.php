<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$cupones = $conn->query("
    SELECT c.id, c.titulo, c.estado, c.fecha_caducidad,
           com.nombre AS comercio, u.nombre AS usuario
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    ORDER BY c.id DESC
");

include "_header.php";
?>

<h1>Cupones</h1>

<div class="card">
    <a class="btn-success" href="nuevo_cupon.php" style="padding:10px 15px;display:inline-block;margin-bottom:15px;">
        ➕ Crear Cupón
    </a>

    <table>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Usuario</th>
            <th>Comercio</th>
            <th>Estado</th>
            <th>Caducidad</th>
            <th>Acciones</th>
        </tr>

        <?php while ($c = $cupones->fetch_assoc()): ?>
        <tr>
            <td><?= $c["id"] ?></td>
            <td><?= htmlspecialchars($c["titulo"]) ?></td>
            <td><?= $c["usuario"] ?: "—" ?></td>
            <td><?= $c["comercio"] ?: "—" ?></td>
            <td><?= strtoupper($c["estado"]) ?></td>
            <td>
                <?= $c["fecha_caducidad"]
                    ? date("d/m/Y", strtotime($c["fecha_caducidad"]))
                    : "—" ?>
            </td>
            <td>
                <a href="ver_cupon_admin.php?id=<?= $c["id"] ?>">👁 Ver</a> |
                <a href="editar_cupon.php?id=<?= $c["id"] ?>">✏ Editar</a> |
                <a href="eliminar_cupon.php?id=<?= $c["id"] ?>"
                   onclick="return confirm('¿Seguro que deseas eliminar este cupón?');">🗑 Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

<?php include "_footer.php"; ?>
