<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener comercios
$sql = $conn->query("
    SELECT id, nombre, telefono, logo
    FROM comercios
    ORDER BY id DESC
");

include "_header.php";
?>

<h1>Comercios</h1>

<?php if (isset($_GET["created"])): ?>
    <div class="card" style="background:#2ecc71;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        ✔ Comercio creado correctamente
    </div>
<?php endif; ?>

<?php if (isset($_GET["deleted"])): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        ✔ Comercio eliminado
    </div>
<?php endif; ?>

<a href="nuevo_comercio.php" class="btn-success" style="margin-bottom:20px;display:inline-block;">
    ➕ Nuevo Comercio
</a>

<div class="card">
<table class="table">
    <tr>
        <th>ID</th>
        <th>Logo</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Acciones</th>
    </tr>

    <?php while ($c = $sql->fetch_assoc()): ?>
        <tr>
            <td><?= $c["id"] ?></td>

            <!-- Mostrar logo si existe -->
            <td style="text-align:center;">
                <?php if ($c["logo"] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/" . $c["logo"])): ?>
                    <img src="/uploads/comercios/<?= $c["logo"] ?>" 
                         style="width:55px;height:55px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                <?php else: ?>
                    <div style="
                        width:55px;height:55px;
                        background:#ccc;
                        border-radius:8px;
                        display:flex;align-items:center;justify-content:center;
                        font-size:12px;color:#555;
                        border:1px solid #aaa;">
                        N/A
                    </div>
                <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($c["nombre"]) ?></td>
            <td><?= htmlspecialchars($c["telefono"] ?: "—") ?></td>

            <td>
                <a href="editar_comercio.php?id=<?= $c['id'] ?>" class="btn btn-small">✏ Editar</a>
                <a href="eliminar_comercio.php?id=<?= $c['id'] ?>" 
                   class="btn-danger btn-small"
                   onclick="return confirm('¿Seguro que quieres eliminar este comercio?');">
                   🗑 Eliminar
                </a>
            </td>
        </tr>
    <?php endwhile; ?>

</table>
</div>

<?php include "_footer.php"; ?>
