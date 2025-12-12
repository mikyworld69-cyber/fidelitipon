<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ACTIVAR DEBUG SOLO SI SE QUIERE VER
$debug = true;  // <-- pon en false cuando acabemos

$mensaje = "";
$color = "#e74c3c";
$logoNombre = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($debug) {
        echo "<pre style='background:#000;color:#0f0;padding:20px;font-size:14px;white-space:pre-wrap;'>";
        echo "==== DEBUG POST ====\n";
        print_r($_POST);
        echo "\n==== DEBUG FILES ====\n";
        print_r($_FILES);
        echo "</pre>";
    }

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre === "") {
        $mensaje = "❌ El nombre del comercio es obligatorio.";
    } else {

        // -------------------------
        // SUBIR LOGO
        // -------------------------
        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {

            $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/";

            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
                $mensaje = "❌ Formato inválido. Usa JPG, PNG o WEBP.";
            } else {

                $logoNombre = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $destino = $uploadsDir . $logoNombre;

                if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
                    $mensaje = "❌ Error moviendo archivo.";
                    $logoNombre = null;
                }
            }
        }

        // -------------------------
        // GUARDAR EN BD
        -------------------------
        if ($mensaje === "") {

            $sql = $conn->prepare("
                INSERT INTO comercios (nombre, telefono, logo)
                VALUES (?, ?, ?)
            ");
            $sql->bind_param("sss", $nombre, $telefono, $logoNombre);

            if ($sql->execute()) {

                if ($debug) {
                    echo "<h2 style='color:yellow;'>DEBUG ACTIVO: No hacemos redirect</h2>";
                } else {
                    header("Location: comercios.php?created=1");
                    exit;
                }

            } else {
                $mensaje = "❌ Error guardando el comercio.";
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
