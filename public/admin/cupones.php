<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ============================
// OBTENER LISTADO DE CUPONES
// ============================
$sql = $conn->query("
    SELECT 
        c.id,
        c.codigo,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_creacion,
        c.fecha_caducidad,
        u.nombre AS nombre_usuario,
        com.nombre AS nombre_comercio
    FROM cupones c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN comercios com ON c.comercio_id = com.id
    ORDER BY c.fecha_creacion DESC
");

include "_header.php";
?>

<h1>Cupones</h1>

<div class="card">

    <table>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Usuario</th>
            <th>Comercio</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Caduca</th>
        </tr>

        <?php while ($c = $sql->fetch_assoc()): ?>

        <?php
            // Badge según estado real
            switch ($c["estado"]) {
                case "activo":
                    $badge = '<span class="badge badge-activo">Activo</span>';
                    break;
                case "usado":
                    $badge = '<span class="badge badge-usado">Usado</span>';
                    break;
                case "caducado":
                    $badge = '<span class="badge badge-caducado">Caducado</span>';
                    break;
                default:
                    $badge = '<span class="badge">'.$c["estado"].'</span>';
            }
        ?>

        <tr>
            <td><?= $c["id"] ?></td>
            <td><?= htmlspecialchars($c["codigo"]) ?></td>
            <td><?= htmlspecialchars($c["nombre_usuario"] ?: "—") ?></td>
            <td><?= htmlspecialchars($c["nombre_comercio"] ?: "—") ?></td>
            <td><?= $badge ?></td>
            <td><?= date("d/m/Y", strtotime($c["fecha_creacion"])) ?></td>
            <td>
                <?= $c["fecha_caducidad"] 
                        ? date("d/m/Y", strtotime($c["fecha_caducidad"]))
                        : "—" ?>
            </td>
        </tr>

        <?php endwhile; ?>

    </table>

</div>

<?php include "_footer.php"; ?>
