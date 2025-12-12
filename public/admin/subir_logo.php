<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

echo "<h2>Subir Logo del Comercio</h2>";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_FILES["logo"]["name"])) {

        $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/";

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0775, true);
        }

        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
            echo "<p style='color:red;'>Formato no válido.</p>";
            exit;
        }

        $fileName = "comercio_" . time() . "_" . rand(1000,9999) . "." . $ext;
        $destino = $uploadsDir . $fileName;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {

            echo "<h3 style='color:green;'>✔ Logo subido correctamente</h3>";
            echo "<p><strong>Archivo:</strong> $fileName</p>";
            echo "<p><img src='/uploads/comercios/$fileName' width='150'></p>";

            echo "<br><a href='nuevo_comercio.php?logo=$fileName' style='font-size:18px;'>⬅ Volver y usar este logo</a>";
            exit;

        } else {
            echo "<p style='color:red;'>Error subiendo archivo.</p>";
            exit;
        }
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label>Seleccionar logo</label>
    <input type="file" name="logo" required>
    <button type="submit">Subir Logo</button>
</form>
