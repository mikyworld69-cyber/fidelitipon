<?php
require_once __DIR__ . '/../../config/db.php';

$token = $_GET["token"] ?? "";
$mensaje = "";

// Validar token existente
$sql = $conn->prepare("SELECT id FROM usuarios WHERE token_recuperacion = ?");
$sql->bind_param("s", $token);
$sql->execute();
$sql->store_result();

if ($sql->num_rows !== 1) {
    die("Token inválido o expirado.");
}

$sql->bind_result($id_user);
$sql->fetch();

// Procesar nueva contraseña
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass1 = trim($_POST["pass1"]);
    $pass2 = trim($_POST["pass2"]);

    if ($pass1 === "" || $pass2 === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif ($pass1 !== $pass2) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        // Guardar nueva contraseña y eliminar token
        $newHash = password_hash($pass1, PASSWORD_BCRYPT);

        $up = $conn->prepare("UPDATE usuarios SET password = ?, token_recuperacion = NULL WHERE id = ?");
        $up->bind_param("si", $newHash, $id_user);
        $up->execute();

        $mensaje = "Contraseña restablecida correctamente. Ya puedes iniciar sesión.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Restablecer contraseña</title>
<link rel="stylesheet" href="/app/app.css">
</head>

<body>

<div class="app-header">Restablecer contraseña</div>

<div class="perfil-box">

    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <label class="label">Nueva contraseña</label>
        <input type="password" name="pass1" required>

        <label class="label">Confirmar contraseña</label>
        <input type="password" name="pass2" required>

        <button class="btn-save">Guardar contraseña</button>
    </form>

    <a class="btn-danger" href="/app/login.php">Volver al inicio</a>
</div>

</body>
</html>
