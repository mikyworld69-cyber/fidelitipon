<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Validar sesión
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$mensaje = "";

// Si envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass_actual = trim($_POST["pass_actual"]);
    $pass_nueva = trim($_POST["pass_nueva"]);
    $pass_confirm = trim($_POST["pass_confirm"]);

    if ($pass_actual === "" || $pass_nueva === "" || $pass_confirm === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif ($pass_nueva !== $pass_confirm) {
        $mensaje = "La nueva contraseña no coincide.";
    } else {
        // Obtener password actual
        $sql = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $sql->bind_param("i", $user_id);
        $sql->execute();
        $sql->bind_result($hash_bd);
        $sql->fetch();

        // Verificar contraseña actual
        if (!password_verify($pass_actual, $hash_bd)) {
            $mensaje = "La contraseña actual es incorrecta.";
        } else {
            // Actualizar contraseña
            $newHash = password_hash($pass_nueva, PASSWORD_BCRYPT);
            $up = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $up->bind_param("si", $newHash, $user_id);
            $up->execute();
            $mensaje = "Contraseña cambiada correctamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña</title>
<link rel="stylesheet" href="/app/app.css">
</head>

<body>

<div class="app-header">Cambiar contraseña</div>

<div class="perfil-box">
    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Contraseña actual</label>
        <input type="password" name="pass_actual" required>

        <label>Nueva contraseña</label>
        <input type="password" name="pass_nueva" required>

        <label>Confirmar nueva contraseña</label>
        <input type="password" name="pass_confirm" required>

        <button class="btn-save">Guardar Cambios</button>
    </form>

    <a class="btn-danger" href="/app/panel_usuario.php">Volver</a>
</div>

</body>
</html>
