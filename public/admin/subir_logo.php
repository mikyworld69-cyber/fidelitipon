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
$exito = false;

// Obtener comercio
$sql = $conn->prepare("SELECT nombre, logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_FILES["logo"]["name"])) {

        $dir = __DIR__ . "/../../public/uploads/comercios/";
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $nombreArchivo = "logo_" . $comercio_id . "_" . time() . ".png";
        $rutaFinal = $dir . $nombreArchivo;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $rutaFinal)) {

            $rutaDB = "uploads/comercios/" . $nombreArchivo;

            $update = $conn->prepare("UPDATE comercios SET logo = ? WHERE id = ?");
            $update->bind_param("si", $rutaDB, $comercio_id);
            $update->execute();

            $exito = true;

            // Redirigir tras 1.5 segundos
            header("refresh:1.5;url=ver_comercio.php?id=" . $comercio_id);

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

<div class="card" style="max-width:450px;margin:auto;text-align:center;">

<?php if ($mensaje): ?>
    <div style="background:#c0392b;color:white;padding:12px;border-radius:6px;margin-bottom:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<?php if ($exito): ?>
    <div style="background:#27ae60;color:white;padding:12px;border-radius:6px;margin-bottom:10px;">
        ✔ Logo actualizado correctamente<br>
        Redirigiendo…
    </div>
    <a href="ver_comercio.php?id=<?= $comercio_id ?>" class="btn" 
       style="background:#3498db;color:white;padding:10px 15px;border-radius:6px;display:inline-block;margin-top:10px;">
       Volver al comercio
    </a>
<?php else: ?>

<p><strong>Comercio:</strong> <?= htmlspecialchars($comercio["nombre"]) ?></p>

<form method="POST" enctype="multipart/form-data" style="text-align:left;">
    <label><strong>Seleccionar imagen</strong></label>
    <input type="file" name="logo" accept="image/*" required>

    <button class="btn-success" style="margin-top:15px;width:100%;">Subir Logo</button>
</form>

<a href="ver_comercio.php?id=<?= $comercio_id ?>" 
   style="display:block;margin-top:15px;text-align:center;">
   ← Volver sin subir
</a>

<?php endif; ?>

</div>

<?php include "_footer.php"; ?>
