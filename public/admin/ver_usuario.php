<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: usuarios.php");
    exit;
}

$id = intval($_GET["id"]);

// Obtener info del usuario
$sql = $conn->prepare("
    SELECT id, nombre, telefono, fecha_registro
    FROM usuarios
    WHERE id = ?
");
$sql->bind_param("i", $id);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();

if (!$user) {
    die("Usuario no encontrado.");
}

// Obtener cupones del usuario
$sqlC = $conn->prepare("
    SELECT id, titulo, estado, fecha_caducidad
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$sqlC->bind_param("i", $id);
$sqlC->execute();
$cupones = $sqlC->get_result();

include "_header.php";
?>

<h1>Usuario: <?= htmlspecialchars($user["nombre"]) ?></h1>

<div class="card">
    <p><strong>ID:</strong> <?= $user["id"] ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($user["telefono"]) ?></p>
    <p><strong>Registrado:</strong> <?= date("d/m/Y H:i", strtotime($user["fecha_registro"])) ?></p>
</div>

<h2>Cupones del Usuario</h2>

<div class="card">

    <?php if ($cupones->num_rows === 0): ?>
        <p style="color:#888;">Este usuario no tiene cupones.</p>
    <?php else: ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Estado</th>
                <th>Caducidad</th>
                <th>Ver</th>
            </tr>

            <?php while ($c = $cupones->fetch_assoc()): ?>
            <tr>
                <td><?= $c["id"] ?></td>
                <td><?= htmlspecialchars($c["titulo"]) ?></td>
                <td><?= strtoupper($c["estado"]) ?></td>
                <td>
                    <?= $c["fecha_caducidad"] 
                        ? date("d/m/Y", strtotime($c["fecha_caducidad"])) 
                        : "—" ?>
                </td>
                <td>
                    <a href="ver_cupon_admin.php?id=<?= $c["id"] ?>">👁 Ver cupón</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    <?php endif; ?>

</div>

<?php include "_footer.php"; ?>
