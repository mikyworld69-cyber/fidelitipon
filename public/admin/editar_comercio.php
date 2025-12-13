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

$logoWeb = "/" . ltrim($comercio["logo"], "/");
?>

<h1>Editar Comercio</h1>

<div class="card">

<form method="POST" action="editar_comercio.php?id=<?= $comercio_id ?>">

    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($comercio["nombre"]) ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($comercio["telefono"]) ?>">

    <?php if (!empty($comercio["logo"])): ?>
        <p><strong>Logo actual:</strong></p>
        <img src="<?= $logoWeb ?>" 
             alt="Logo comercio" 
             style="max-width:150px; border:1px solid #ccc; padding:5px; border-radius:8px;">
        <br><br>
    <?php endif; ?>

    <a href="subir_logo.php?id=<?= $comercio_id ?>" class="btn-success">Cambiar Logo</a>

    <br><br>
    <button class="btn-success">Guardar cambios</button>
    <a href="comercios.php" class="btn">Volver</a>

</form>

</div>

<?php include "_footer.php"; ?>
