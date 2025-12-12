<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$comercio_id = intval($_GET["id"]);

// Obtener datos del comercio
$sql = $conn->prepare("SELECT nombre, telefono, logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    die("Comercio no encontrado.");
}

$mensaje = "";
$color = "#e74c3c";

// ------------------------------
// PROCESAR FORMULARIO
// ------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre    = trim($_POST["nombre"]);
    $telefono  = trim($_POST["telefono"]);
    $logo_new  = isset($_POST["logo_final"]) ? trim($_POST["logo_final"]) : null;

    // Si no hay logo nuevo → mantener el anterior
    $logo_final = $logo_new ?: $comercio["logo"];

    if ($nombre === "") {
        $mensaje = "❌ El nombre del comercio es obligatorio.";
    } else {

        $update = $conn->prepare("
            UPDATE comercios 
            SET nombre = ?, telefono = ?, logo = ?
            WHERE id = ?
        ");

        $update->bind_param("sssi", $nombre, $telefono, $logo_final, $comercio_id);

        if ($update->execute()) {
            header("Location: comercios.php?updated=1");
            exit;
        } else {
            $mensaje = "❌ Error actualizando el comercio.";
        }
    }
}

include "_header.php";
?>

<h1>Editar Comercio</h1>

<?php if ($mensaje): ?>
    <div class="card" style="background:<?= $color ?>;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<div class="card">

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($comercio["nombre"]) ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($comercio["telefono"]) ?>">

    <label>Logo Actual</label>

    <?php if ($comercio["logo"] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/" . $comercio["logo"])): ?>
        <div style="margin-bottom:10px;">
            <img src="/uploads/comercios/<?= $comercio["logo"] ?>" 
                 width="120" style="border-radius:8px;border:1px solid #ddd;">
        </div>
    <?php else: ?>
        <p style="color:#777;">No tiene logo</p>
    <?php endif; ?>

    <?php if (isset($_GET["logo"])): ?>
        <p>Nuevo logo seleccionado:</p>
        <img src="/uploads/comercios/<?= $_GET["logo"] ?>" 
             width="120" style="border-radius:8px;">
        <input type="hidden" name="logo_final" value="<?= $_GET["logo"] ?>">
    <?php endif; ?>

    <a href="subir_logo.php" 
       style="display:inline-block;margin-top:8px;padding:8px 12px;background:#3498db;color:white;border-radius:8px;text-decoration:none;">
       📤 Subir Nuevo Logo
    </a>

    <br><br>

    <button class="btn-success">Guardar Cambios</button>

</form>

</div>

<?php include "_footer.php"; ?>
