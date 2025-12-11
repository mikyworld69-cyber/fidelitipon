<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar ID de usuario
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$user_id = intval($_GET["id"]);

// ==============================
// OBTENER DATOS DEL USUARIO
// ==============================
$sql = $conn->prepare("
    SELECT id, nombre, telefono, email
    FROM usuarios
    WHERE id = ?
");
$sql->bind_param("i", $user_id);
$sql->execute();
$usuario = $sql->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}

$mensaje = "";

// ==============================
// PROCESAR FORMULARIO
// ==============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre    = trim($_POST["nombre"]);
    $telefono  = trim($_POST["telefono"]);
    $email     = trim($_POST["email"]);
    $password  = trim($_POST["password"]);

    if ($nombre === "" || $telefono === "") {
        $mensaje = "❌ Nombre y teléfono son obligatorios.";
    } else {

        // Si el admin quiere cambiar contraseña
        if ($password !== "") {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $update = $conn->prepare("
                UPDATE usuarios 
                SET nombre = ?, telefono = ?, email = ?, password = ?
                WHERE id = ?
            ");
            $update->bind_param("ssssi", $nombre, $telefono, $email, $hash, $user_id);

        } else {
            // Actualización sin contraseña
            $update = $conn->prepare("
                UPDATE usuarios 
                SET nombre = ?, telefono = ?, email = ?
                WHERE id = ?
            ");
            $update->bind_param("sssi", $nombre, $telefono, $email, $user_id);
        }

        if ($update->execute()) {
            header("Location: editar_usuario.php?id=$user_id&ok=1");
            exit;
        } else {
            $mensaje = "❌ Error actualizando usuario.";
        }
    }
}

include "_header.php";
?>

<h1>Editar Usuario</h1>

<?php if (isset($_GET["ok"])): ?>
    <div class="card" style="background:#2ecc71;color:white;padding:12px;border-radius:10px;">
        ✔ Usuario actualizado correctamente
    </div>
<?php endif; ?>

<?php if ($mensaje): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<div class="card">

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

    <label>Teléfono *</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">

    <label>Cambiar contraseña (opcional)</label>
    <input type="password" name="password" placeholder="Dejar vacío para no cambiar">

    <button class="btn-success" style="margin-top:15px;">Guardar Cambios</button>

</form>

</div>

<?php include "_footer.php"; ?>
