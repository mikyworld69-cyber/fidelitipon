<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// GUARDAR NUEVO COMERCIO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    $logo_rel = null;

    // ----------------------------
    // SUBIDA DE LOGO AL DISCO PERSISTENTE
    // ----------------------------
    if (!empty($_FILES["logo"]["name"])) {

        $upload_dir = "/var/data/uploads/comercios/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $newname = "comercio_" . time() . "." . $ext;
        $destino = $upload_dir . $newname;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
            // ruta relativa para file.php
            $logo_rel = "uploads/comercios/" . $newname;
        }
    }

    $sql = $conn->prepare("
        INSERT INTO comercios (nombre, telefono, logo)
        VALUES (?, ?, ?)
    ");
    $sql->bind_param("sss", $nombre, $telefono, $logo_rel);

    if ($sql->execute()) {
        header("Location: comercios.php");
        exit;
    } else {
        $mensaje = "❌ Error al crear el comercio";
    }
}

include "_header.php";
?>

<h1>Nuevo Comercio</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="error" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Nombre del comercio *</label>
    <input type="text" name="nombre" required>

    <label>Teléfono</label>
    <input type="text" name="telefono">

    <label>Logo (opcional)</label>
    <input type="file" name="logo" accept="image/*">

    <button class="btn-success" style="margin-top:15px;">Crear Comercio</button>

</form>

</div>

<?php include "_footer.php"; ?>
