<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: usuarios.php");
    exit;
}

$id = intval($_GET["id"]);

// Cargar usuario
$sql = $conn->prepare("SELECT id, nombre, telefono FROM usuarios WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();

if (!$user) {
    die("Usuario no encontrado.");
}

$mensaje = "";

// Guardar cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    $up = $conn->prepare("
        UPDATE usuarios
        SET nombre = ?, telefono = ?
        WHERE id = ?
    ");
    $up->bind_param("ssi", $nombre, $telefono, $id);

    if ($up->execute()) {
        header("Location: ver_usuario.php?id=" . $id);
        exit;
    } else {
        $mensaje = "❌ Error al actualizar usuario.";
    }
}

include "_header.php";
?>

<h1>Editar Usuario</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="error" style="background:#c0392b;color:white;padding:10px;border-radius:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<form method="POST">
    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($user["nombre"]) ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($user["telefono"]) ?>">

    <button class="btn-success" style="margin-top:15px;">Guardar Cambios</button>
</form>

</div>

<?php include "_footer.php"; ?>
