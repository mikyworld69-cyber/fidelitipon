<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color = "#e74c3c";

// =====================================================
// DEBUG: PARA SABER EXACTAMENTE QUÉ LLEGA DESDE EL FORM
// =====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    echo "<pre style='background:#000;color:#0f0;padding:20px;font-size:14px;white-space:pre-wrap;'>";
    echo "==== DEBUG POST ====\n";
    print_r($_POST);
    echo "\n==== DEBUG FILES ====\n";
    print_r($_FILES);
    echo "</pre>";
}

// =====================================================
// PROCESAR FORMULARIO
// =====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $logoNombre = null;

    if ($nombre === "") {
        $mensaje = "❌ El nombre del comercio es obligatorio.";
    } else {

        // -------------------------------------
        // SUBIR LOGO (si se ha enviado)
        // -------------------------------------
        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {

            // Carpeta correcta de Render
            $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/";

            // Crear carpeta si no existe
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
                $mensaje = "❌ Formato de imagen no válido. Solo JPG, PNG, WEBP.";
            } else {

                // Nombre único
                $logoNombre = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;

                $destino = $uploadsDir . $logoNombre;

                // Intentar mover el archivo temporal
                if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
                    $mensaje = "❌ ERROR moviendo archivo. Ruta usada:<br>$destino";
                }
            }
        }

        // -------------------------------------
        // GUARDAR COMERCIO SI NO HAY ERRORES
        // -------------------------------------
        if ($mensaje === "") {
            $sql = $conn->prepare("
                INSERT INTO comercios (nombre, telefono, logo)
                VALUES (?, ?, ?)
            ");
            $sql->bind_param("sss", $nombre, $telefono, $logoNombre);

            if ($sql->execute()) {
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
