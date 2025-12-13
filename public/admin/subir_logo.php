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
$mensaje = "";

// Obtener comercio
$sql = $conn->prepare("SELECT nombre, logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

// PROCESAR SUBIDA
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_FILES["logo"]["name"])) {

        $dir = __DIR__ . "/../../public/uploads/comercios/";
        $nombreArchivo = "logo_" . $comercio_id . "_" . time() . ".png";
        $rutaFinal = $dir . $nombreArchivo;

        if (!is_dir($dir)) mkdir($dir, 0775, true);

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $rutaFinal)) {

            // Guardar en BD
            $rutaDB = "uploads/comercios/" . $nombreArchivo;

            $update = $conn->prepare("UPDATE comercios SET logo = ? WHERE id = ?");
            $update->bind_param("si", $rutaDB, $comercio_id);
            $update->execute();

            // REDIRIGIR AUTOMÁTICAMENTE
            header("Location: ver_comercio.php?id=" . $comercio_id);
            exit;

        } else {
            $mensaje = "Error al subir archivo.";
        }
    } else {
        $mensaje = "No se seleccionó ninguna imagen.";
    }
}

include "_header.php";
?>

<h1>Subir Logo</h1>

<div class="card" style="max-width:450px;margin:auto;">

<?php if ($mensaje): ?>
    <div style="background:#c0392b;color:white;padding:10px;border-radius:6px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<p><strong>Comercio:</strong> <?= htmlspecialchars($comercio["nombre"]) ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Seleccionar imagen</label>
    <input type="file" name="logo" accept="image/*" required>

    <button class="btn-success" style="margin-top:15px;">Subir Logo</button>
</form>

</div>

<?php include "_footer.php"; ?>
