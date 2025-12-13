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
$sql = $conn->prepare("SELECT nombre, logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $com_id);
$sql->execute();
$com = $sql->get_result()->fetch_assoc();

// Procesar subida
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($_FILES["logo"]["error"] === 0) {

        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "webp"];

        if (in_array($ext, $permitidas)) {

            $nuevo = "logo_" . $com_id . "." . $ext;
            $ruta = __DIR__ . "/../../public/uploads/comercios/" . $nuevo;

            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $ruta)) {

                $up = $conn->prepare("UPDATE comercios SET logo=? WHERE id=?");
                $up->bind_param("si", $nuevo, $com_id);
                $up->execute();

                $mensaje = "✔ Logo actualizado";
                $color_msg = "#27ae60";
                $com["logo"] = $nuevo;

            } else {
                $mensaje = "No se pudo mover el archivo.";
            }

        } else {
            $mensaje = "Formato no permitido.";
        }

    } else {
        $mensaje = "Error al subir archivo.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Subir Logo</title>
<link rel="stylesheet" href="admin.css">

<style>
.box {
    max-width:420px;
    margin:30px auto;
    background:white;
    padding:25px;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.12);
    font-family:Arial;
}
.btn { width:100%;padding:14px;background:#3498db;color:white;border:none;border-radius:10px;font-size:16px;cursor:pointer; }
input[type=file] { width:100%;padding:12px;margin-bottom:20px;border:1px solid #ccc;border-radius:10px; }
.img-prev { text-align:center;margin-bottom:20px; }
.img-prev img { max-width:200px;border-radius:12px;box-shadow:0 3px 10px rgba(0,0,0,0.15); }
.msg { padding:12px;color:white;border-radius:10px;text-align:center;margin-bottom:15px; }
</style>

</head>
<body>

<div class="box">

    <h2 style="text-align:center;color:#3498db;">Subir Logo<br><?= htmlspecialchars($com["nombre"]) ?></h2>

    <?php if ($mensaje): ?>
        <div class="msg" style="background:<?= $color_msg ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="img-prev">
        <?php if ($com["logo"]): ?>
            <img src="/uploads/comercios/<?= $com["logo"] ?>">
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="logo" required>
        <button class="btn">Guardar Logo</button>
    </form>
</div>

</body>
</html>
