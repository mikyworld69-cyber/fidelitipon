<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$logoNombre = null;

/*************************************************
 * PRIMERO — PROCESAR FORMULARIO
 * (ANTES DE IMPRIMIR HTML O INCLUDES)
*************************************************/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre === "") {
        $mensaje = "❌ El nombre es obligatorio.";
    } else {

        /** SUBIR LOGO **/
        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {

            $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/";
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0775, true);

            $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

            if (in_array($ext, ["jpg", "jpeg", "png", "webp"])) {

                $logoNombre = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $destino = $uploadsDir . $logoNombre;

                if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
                    $mensaje = "❌ Error moviendo archivo.";
                    $logoNombre = null;
                }

            } else {
                $mensaje = "❌ Formato inválido.";
            }
        }

        /** GUARDAR SOLO SI NO HUBO ERRORES **/
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
                $mensaje = "❌ Error guardando en BD.";
            }
        }
    }
}

/*************************************************
 * DESPUÉS — INCLUDES Y HTML
*************************************************/
include "_header.php";
?>

<h1>Crear Comercio</h1>

<?php if ($mensaje): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;">
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
    <input type="file" name="logo">

    <button class="btn-success">Crear Comercio</button>

</form>

</div>

<?php include "_footer.php"; ?>
