<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color = "#e74c3c";

// Procesar formulario (IMPORTANTE: antes de imprimir HTML)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre      = trim($_POST["nombre"]);
    $telefono    = trim($_POST["telefono"]);
    $logo_final  = isset($_POST["logo_final"]) ? trim($_POST["logo_final"]) : null;

    if ($nombre === "") {
        $mensaje = "❌ El nombre del comercio es obligatorio.";
    } else {

        // Insertar en BD
        $sql = $conn->prepare("
            INSERT INTO comercios (nombre, telefono, logo)
            VALUES (?, ?, ?)
        ");

        $sql->bind_param("sss", $nombre, $telefono, $logo_final);

        if ($sql->execute()) {
            header("Location: comercios.php?created=1");
            exit;
        } else {
            $mensaje = "❌ Error guardando el comercio en la base de datos.";
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

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" required>

    <label>Teléfono</label>
    <input type="text" name="telefono">

    <label>Logo del Comercio</label>

    <?php if (isset($_GET["logo"])): ?>
        <p>Logo seleccionado:</p>
        <img src="/uploads/comercios/<?= $_GET["logo"] ?>" width="120" style="border-radius:8px;">
        <input type="hidden" name="logo_final" value="<?= $_GET["logo"] ?>">
    <?php else: ?>
        <p style="color:#777;">Ningún logo seleccionado</p>
    <?php endif; ?>

    <a href="subir_logo.php" class="btn" 
       style="display:inline-block;padding:10px 15px;background:#3498db;color:white;border-radius:10px;margin-top:10px;text-decoration:none;">
        📤 Subir Logo
    </a>

    <button class="btn-success" style="margin-top:20px;">Crear Comercio</button>

</form>

</div>

<?php include "_footer.php"; ?>
