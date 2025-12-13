<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

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
$com = $sql->get_result()->fetch_assoc();

if (!$com) {
    die("Comercio no encontrado.");
}

// ----------------------------
// Procesar subida (NO tocar)
// ----------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === 0) {

        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "webp"];

        if (in_array($ext, $permitidas)) {

            $nuevoNombre = "logo_" . $com_id . "." . $ext;
            $ruta = __DIR__ . "/../../public/uploads/comercios/" . $nuevoNombre;

            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $ruta)) {

                $update = $conn->prepare("UPDATE comercios SET logo = ? WHERE id = ?");
                $update->bind_param("si", $nuevoNombre, $com_id);
                $update->execute();

                $mensaje = "✔ Logo actualizado correctamente.";
                $color_msg = "#27ae60";

                // Actualizar valor en memoria
                $com["logo"] = $nuevoNombre;

            } else {
                $mensaje = "No se pudo mover el archivo.";
            }

        } else {
            $mensaje = "Formato no permitido (solo JPG, PNG, WEBP).";
        }

    } else {
        $mensaje = "Error al subir archivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Subir Logo</title>
<link rel="stylesheet" href="admin.css">

<style>
body {
    background:#f4f6f9;
    font-family: Arial, sans-serif;
}

.box {
    width: 95%;
    max-width: 420px;
    background: white;
    padding: 25px;
    margin: 40px auto;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

h2 {
    text-align:center;
    margin-bottom:20px;
    color:#3498db;
    font-size:22px;
}

.msg {
    padding:12px;
    border-radius:10px;
    color:white;
    text-align:center;
    margin-bottom:20px;
    font-size:15px;
}

.logo-prev {
    text-align:center;
    margin-bottom:20px;
}

.logo-prev img {
    max-width:200px;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.15);
}

input[type="file"] {
    width:100%;
    padding:12px;
    margin-bottom:20px;
    border:1px solid #ccc;
    border-radius:10px;
}

.btn {
    width:100%;
    padding:14px;
    background:#3498db;
    color:white;
    border:none;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}

.btn:hover {
    background:#2980b9;
}
</style>

<script>
function previewImg(event) {
    const img = document.getElementById("prevLogo");
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = "block";
}
</script>
</head>

<body>

<div class="box">

    <h2>Subir Logo<br><?= htmlspecialchars($com["nombre"]) ?></h2>

    <?php if ($mensaje): ?>
        <div class="msg" style="background: <?= $color_msg ?>;">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="logo-prev">
        <?php if (!empty($com["logo"])): ?>
            <img id="prevLogo" src="/uploads/comercios/<?= $com["logo"] ?>" alt="Logo actual">
        <?php else: ?>
            <img id="prevLogo" style="display:none;">
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="logo" accept="image/*" onchange="previewImg(event)" required>
        <button class="btn">Guardar Logo</button>
    </form>

</div>

</body>
</html>
