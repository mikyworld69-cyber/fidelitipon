<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$comercio_id = intval($_GET["id"]);
$mensaje = "";

// Obtener comercio
$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);

    $update = $conn->prepare("UPDATE comercios SET nombre = ? WHERE id = ?");
    $update->bind_param("si", $nombre, $comercio_id);

    if ($update->execute()) {
        header("Location: ver_comercio.php?id=" . $comercio_id);
        exit;
    } else {
        $mensaje = "Error al actualizar comercio.";
    }
}

include "_header.php";
?>

<h1>Editar Comercio</h1>

<div class="card" style="max-width:450px;margin:auto;">

<?php if ($mensaje): ?>
    <div class="error"><?= $mensaje ?></div>
<?php endif; ?>

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($comercio["nombre"]) ?>" required>

    <p style="margin-top:15px;">
        <strong>Logo actual:</strong><br>
        <?php if ($comercio["logo"]): ?>
            <img src="/<?= $comercio["logo"] ?>" style="max-width:150px;border-radius:8px;margin-top:10px;">
        <?php else: ?>
            <em>No hay logo</em>
        <?php endif; ?>
    </p>

    <a href="subir_logo.php?id=<?= $comercio_id ?>" class="btn-secondary">Subir nuevo logo</a>

    <button class="btn-success" style="margin-top:15px;">Guardar cambios</button>

</form>

</div>

<?php include "_footer.php"; ?>
