<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: cupones.php");
    exit;
}

$id = intval($_GET["id"]);

// Obtener cupón
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, fecha_caducidad, estado
    FROM cupones
    WHERE id = ?
");
$sql->bind_param("i", $id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) die("Cupón no encontrado.");

$mensaje = "";

// GUARDAR CAMBIOS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;
    $estado = $_POST["estado"];

    $up = $conn->prepare("
        UPDATE cupones
        SET titulo = ?, descripcion = ?, fecha_caducidad = ?, estado = ?
        WHERE id = ?
    ");
    $up->bind_param("ssssi", $titulo, $descripcion, $fecha_caducidad, $estado, $id);

    if ($up->execute()) {
        header("Location: ver_cupon_admin.php?id=$id");
        exit;
    } else {
        $mensaje = "❌ Error al actualizar cupón.";
    }
}

include "_header.php";
?>

<h1>Editar Cupón</h1>

<div class="card">

<?php if ($mensaje): ?>
<div class="error" style="background:#c0392b;padding:10px;color:white;border-radius:10px;">
    <?= $mensaje ?>
</div>
<?php endif; ?>

<form method="POST">

    <label>Título *</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($cup["titulo"]) ?>" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="3"><?= htmlspecialchars($cup["descripcion"]) ?></textarea>

    <label>Fecha de caducidad</label>
    <input type="date" name="fecha_caducidad"
           value="<?= $cup["fecha_caducidad"] ?: "" ?>">

    <label>Estado</label>
    <select name="estado">
        <option value="activo"   <?= $cup["estado"] == "activo" ? "selected" : "" ?>>Activo</option>
        <option value="usado"    <?= $cup["estado"] == "usado"  ? "selected" : "" ?>>Usado</option>
        <option value="caducado" <?= $cup["estado"] == "caducado" ? "selected" : "" ?>>Caducado</option>
    </select>

    <button class="btn-success" style="margin-top:15px;">Guardar Cambios</button>

</form>

</div>

<?php include "_footer.php"; ?>
