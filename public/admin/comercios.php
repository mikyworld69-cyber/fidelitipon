<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ELIMINAR COMERCIO
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    $del = $conn->prepare("DELETE FROM comercios WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: comercios.php");
    exit;
}

// LISTADO
$sql = $conn->query("
    SELECT id, nombre, direccion, telefono, responsable, fecha_registro
    FROM comercios
    ORDER BY fecha_registro DESC
");

include "_header.php";
?>

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
                   onclick="return confirm('¿Eliminar este comercio? También se eliminarán cupones asociados.')">
                   🗑
                </a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include "_footer.php"; ?>
