<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color_msg = "#e74c3c";

// Crear comercio
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre !== "") {

        $sql = $conn->prepare("
            INSERT INTO comercios (nombre, telefono)
            VALUES (?, ?)
        ");
        $sql->bind_param("ss", $nombre, $telefono);

        if ($sql->execute()) {

            $newID = $conn->insert_id;

            header("Location: subir_logo.php?id=" . $newID);
            exit;

        } else {
            $mensaje = "Error al guardar.";
        }

    } else {
        $mensaje = "El nombre es obligatorio.";
    }
}

include "_header.php";
?>

<h1>Crear Comercio</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="msg" style="background:<?= $color_msg ?>;color:white;padding:12px;border-radius:10px;"><?= $mensaje ?></div>
<?php endif; ?>

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" required>

    <label>Teléfono (opcional)</label>
    <input type="text" name="telefono">

    <button class="btn-success" style="margin-top:15px;">Guardar Comercio</button>

</form>

</div>

<?php include "_footer.php"; ?>
