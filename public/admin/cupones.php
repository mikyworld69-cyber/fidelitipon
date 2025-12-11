<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener todos los cupones
$cupones = $conn->query("
    SELECT 
        c.id, c.codigo, c.titulo, c.estado, c.fecha_caducidad,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    ORDER BY c.id DESC
");
?>

<?php include "_header.php"; ?>

<h1>Cupones</h1>

<?php if (isset($_GET["deleted"])): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:10px;border-radius:10px;margin-bottom:15px;">
        ✔ Cupón eliminado correctamente
    </div>
<?php endif; ?>

<div style="text-align:right;">
    <a href="nuevo_cupon.php" class="btn btn-success">➕ Crear Cupón</a>
</div>

<div class="card">
<table>
    <tr>
        <th>ID</th>
        <th>Código</th>
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
        <td><?= $c["codigo"] ?></td>
        <td><?= htmlspecialchars($c["titulo"]) ?></td>
        <td><?= $c["usuario_nombre"] ?: "—" ?></td>
        <td><?= $c["comercio_nombre"] ?></td>
        <td><?= strtoupper($c["estado"]) ?></td>
        <td>
            <?= $c["fecha_caducidad"] 
                ? date("d/m/Y H:i", strtotime($c["fecha_caducidad"])) 
                : "—" 
            ?>
        </td>

        <td>
            <a class="btn btn-small" href="ver_cupon_admin.php?id=<?= $c['id'] ?>">👁 Ver</a>
            <a class="btn btn-small" href="editar_cupon.php?id=<?= $c['id'] ?>">✏ Editar</a>
            <a class="btn btn-small btn-danger"
               href="eliminar_cupon.php?id=<?= $c['id'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar este cupón?');">
               ❌
            </a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>
</div>

<?php include "_footer.php"; ?>
