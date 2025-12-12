<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color = "#e74c3c";
$logoNombre = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre === "") {

        $mensaje = "❌ El nombre del comercio es obligatorio.";

    } else {

        /* ======================================================
           SUBIR LOGO (si existe)
        ====================================================== */

        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {

            // Carpeta válida en Render
            $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/";

            // Crear carpeta si no existe
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0775, true);
            }

            // Extensión del archivo
            $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
                $mensaje = "❌ Formato inválido. Usa JPG, PNG o WEBP.";
            } else {

                // Nombre único del archivo
                $logoNombre = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;

                $destino = $uploadsDir . $logoNombre;

                // Intentar mover archivo subido
                if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
                    $mensaje = "❌ Error subiendo el archivo al servidor.";
                    $logoNombre = null;
                }
            }
        }

        /* ======================================================
           GUARDAR EN BASE DE DATOS
        ====================================================== */

        if ($mensaje === "") {

            $sql = $conn->prepare("
                INSERT INTO comercios (nombre, telefono, logo)
                VALUES (?, ?, ?)
            ");
            $sql->bind_param("sss", $nombre, $telefono, $logoNombre);

            if ($sql->execute()) {

                // 🎯 TODO OK → Redirigir
                header("Location: comercios.php?created=1");
                exit;

            } else {
                $mensaje = "❌ Error guardando el comercio en la base de datos.";
            }
        }
    }
}

include "_header.php";
?>

<h1>Crear Comercio</h1>

<?php if ($mensaje): ?>
    <div class="card" style="background:<?= $color ?>;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
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
