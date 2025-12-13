<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Comercio no válido.");
}

$comercio_id = intval($_GET["id"]);

$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

include "_header.php";

// Ruta correcta del logo
$logoWeb = "/" . ltrim($comercio["logo"], "/");
?>

<h1>Comercio: <?= htmlspecialchars($comercio["nombre"]) ?></h1>

<div class="card">

    <?php if (!empty($comercio["logo"])): ?>
        <p><strong>Logo:</strong></p>
        <img src="<?= $logoWeb ?>" 
             alt="Logo comercio" 
             style="max-width:150px; border:1px solid #ccc; padding:5px; border-radius:8px;">
        <br><br>
    <?php endif; ?>

    <p><strong>Nombre:</strong> <?= htmlspecialchars($comercio["nombre"]) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($comercio["telefono"]) ?></p>

    <br>
    <a href="editar_comercio.php?id=<?= $comercio_id ?>" class="btn-warning">Editar</a>
    <a href="comercios.php" class="btn">Volver</a>

</div>

<?php include "_footer.php"; ?>
