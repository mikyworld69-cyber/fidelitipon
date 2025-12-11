<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color = "#e74c3c";

// ==============================
// PROCESAR FORMULARIO
// ==============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre === "") {
        $mensaje = "❌ El nombre del comercio es obligatorio.";
    } else {

        // Procesar subida de logo si existe
        $logoNombre = null;

        if (!empty($_FILES["logo"]["name"])) {

            // Carpeta destino
            $uploadsDir = __DIR__ . "/../../uploads/comercios/";
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

            // Permitir solo imágenes
            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
                $mensaje = "❌ Formato de imagen no válido (solo JPG, PNG, WEBP).";
            } else {

                $logoNombre = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;

                move_uploaded_file(
                    $_FILES["logo"]["tmp_name"],
                    $uploadsDir . $logoNombre
                );
            }
        }

        // Insertar comercio
        $sql = $conn->prepare("
            INSERT INTO comercios (nombre, telefono, logo)
            VALUES (?, ?, ?)
        ");
        $sql->bind_param("sss", $nombre, $telefono, $logoNombre);

        if ($sql->execute()) {
            header("Location: comercios.php?created=1");
            exit;
        } else {
            $mensaje = "❌ Error guardando el comercio.";
        }
    }
}

include "_header.php";
?>

<h1>Crear Comercio</h1>

<?php if ($mensaje): ?>
    <div class="card" style="background:<?= $color ?>;color:white;padding:12px;border-radius:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<div class="card">

<form method="POST" enctype="multipart/form-data">

    <label>Nombre *</label>
    <input type="text" name="nombre" required>

    <label>Teléfono</label>
    <input type="text" name="telefono">

    <label>Logo (opcional)</label>
    <input type="file" name="logo" accept="image/*">

    <button class="btn-success" style="margin-top:15px;">Crear Comercio</button>

</form>

</div>

<?php include "_footer.php"; ?>
