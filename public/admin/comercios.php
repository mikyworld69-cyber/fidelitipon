<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener lista de comercios
$comercios = $conn->query("
    SELECT 
        c.id,
        c.nombre,
        c.telefono,
        c.logo,
        (SELECT COUNT(*) FROM cupones WHERE comercio_id = c.id) AS total_cupones
    FROM comercios c
    ORDER BY c.id DESC
");
?>

<?php include "_header.php"; ?>

<h1>Comercios</h1>

<?php if (isset($_GET["deleted"])): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        ✔ Comercio eliminado correctamente
    </div>
<?php endif; ?>

<div style="text-align:right;margin-bottom:15px;">
    <a href="nuevo_comercio.php" class="btn btn-success">➕ Crear Comercio</a>
</div>

<div class="card">
<table>
    <tr>
        <th>ID</th>
        <th>Logo</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Cupones</th>
        <th>Acciones</th>
    </tr>

    <?php while ($com = $comercios->fetch_assoc()): ?>
    <tr>
        <td><?= $com["id"] ?></td>

        <td>
            <?php if ($com["logo"] && file_exists(__DIR__ . "/../../uploads/comercios/" . $com["logo"])): ?>
                <img src="/uploads/comercios/<?= $com["logo"] ?>" 
                     style="width:40px;height:40px;border-radius:6px;object-fit:cover;">
            <?php else: ?>
                <span style="color:#bbb;">Sin logo</span>
            <?php endif; ?>
        </td>

        <td><?= htmlspecialchars($com["nombre"]) ?></td>
        <td><?= htmlspecialchars($com["telefono"] ?: "—") ?></td>
        <td><?= $com["total_cupones"] ?></td>

        <td>
            <a class="btn btn-small" href="ver_comercio.php?id=<?= $com['id'] ?>">👁 Ver</a>
            <a class="btn btn-small" href="editar_comercio.php?id=<?= $com['id'] ?>">✏ Editar</a>
            <a class="btn btn-small btn-danger"
               href="eliminar_comercio.php?id=<?= $com['id'] ?>"
               onclick="return confirm('¿Eliminar este comercio? Sus cupones también quedarán sin referencia.');">
               ❌
            </a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>
</div>

<?php include "_footer.php"; ?>
