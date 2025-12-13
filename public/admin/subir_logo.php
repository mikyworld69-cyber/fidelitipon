<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Asegurar login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("ID no recibido.");
}

$com_id = intval($_GET["id"]);
$mensaje = "";
$color_msg = "#e74c3c";

// Obtener comercio
$sql = $conn->prepare("SELECT id, nombre, logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $com_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

// Procesar subida
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["logo"])) {

    $file = $_FILES["logo"];

    if ($file["error"] === 0) {

        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "webp"];

        if (in_array($ext, $permitidas)) {

            $nuevoNombre = "logo_" . $com_id . "." . $ext;
            $ruta = __DIR__ . "/../../public/uploads/comercios/" . $nuevoNombre;

            if (move_uploaded_file($file["tmp_name"], $ruta)) {

                $up = $conn->prepare("UPDATE comercios SET logo = ? WHERE id = ?");
                $up->bind_param("si", $nuevoNombre, $com_id);
                $up->execute();

                $mensaje = "✔ Logo actualizado correctamente.";
                $color_msg = "#27ae60";

            } else {
                $mensaje = "No se pudo mover el archivo.";
            }

        } else {
            $mensaje = "Formato no permitido. Solo JPG, PNG o WEBP.";
        }

    } else {
        $mensaje = "Error al subir el archivo.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Subir Logo | Fidelitipon</title>
<link rel="stylesheet" href="admin.css">

<style>
body {
    background:#f5f6fa;
    font-family: Arial, sans-serif;
}

.contenedor {
    width: 95%;
    max-width: 430px;
    margin: 30px auto;
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
}

h2 {
    text-align:center;
    color:#3498db;
    margin-bottom:20px;
    font-size:22px;
}

.msg {
    padding:12px;
    color:white;
    border-radius:10px;
    margin-bottom:20px;
    text-align:center;
    font-size:15px;
}

input[type="file"] {
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    background:white;
    margin-bottom:20px;
}

.btn {
    width:100%;
    padding:14px;
    background:#3498db;
    color:white;
    text-align:center;
    border-radius:10px;
    font-size:16px;
    border:none;
    cursor:pointer;
}

.btn:hover {
    background:#2980b9;
}

.preview {
    text-align:center;
    margin-bottom:20px;
}

.preview img {
    max-width:200px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,0.15);
}
</style>

<script>
function verPreview(event) {
    const img = document.getElementById("previewImg");
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = "block";
}
</script>

</head>
<body>

<div class="contenedor">

    <h2>Subir Logo<br><?= htmlspecialchars($comercio["nombre"]) ?></h2>

    <?php if ($mensaje): ?>
        <div class="msg" style="background:<?= $color_msg ?>;">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="preview">
        <?php if ($comercio["logo"]): ?>
            <img id="previewImg" src="/uploads/comercios/<?= $comercio["logo"] ?>" alt="Logo">
        <?php else: ?>
            <img id="previewImg" style="display:none;">
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="logo" accept="image/*" onchange="verPreview(event)" required>
        <button class="btn">Guardar Logo</button>
    </form>

</div>

</body>
</html>
