<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado, lo mandamos al panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

if (isset($_POST["registrar"])) {

    $telefono  = trim($_POST["telefono"]);
    $password  = trim($_POST["password"]);
    $nombre    = trim($_POST["nombre"] ?? "");

    // Validación básica
    if ($telefono === "" || $password === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } else {

        // Comprobar si existe el teléfono
        $sql = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
        $sql->bind_param("s", $telefono);
        $sql->execute();
        $sql->store_result();

        if ($sql->num_rows > 0) {
            $mensaje = "Este teléfono ya está registrado.";
        } else {

            // Hash contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $ins = $conn->prepare("
                INSERT INTO usuarios (telefono, password_hash, nombre)
                VALUES (?, ?, ?)
            ");
            $ins->bind_param("sss", $telefono, $password_hash, $nombre);
            $ins->execute();

            // Autologin
            $_SESSION["usuario_id"] = $ins->insert_id;

            header("Location: panel_usuario.php");
            exit;
        }
    }
}
?>
<link rel="stylesheet" href="/public/app/app.css">

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrarse | Fidelitipon</title>

<link rel="stylesheet" href="/public/app/app.css">

<style>
/* Estilos mínimos adicionales para centrar el formulario */
.login-container {
    max-width: 420px;
    margin: 60px auto;
}
</style>
<link rel="manifest" href="/public/manifest.json">
<meta name="theme-color" content="#3498db">

</head>
<body>

<div class="container login-container">
    
    <div class="card">
        <h2>Crear Cuenta</h2>

        <?php if ($mensaje): ?>
            <div class="msg" style="background:#c0392b; color:white; padding:12px; border-radius:10px; margin-bottom:15px;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Nombre (opcional)</label>
            <input type="text" name="nombre" placeholder="Tu nombre">

            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="Ej: 600123456" required>

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>

            <button class="btn mt-20" type="submit" name="registrar">Crear Cuenta</button>

        </form>

        <p style="margin-top:15px; text-align:center;">
            ¿Ya tienes cuenta?
            <a href="login.php">Inicia sesión</a>
        </p>
    </div>

</div>
<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/public/sw-pwa.js")
        .then(() => console.log("SW PWA registrado"))
        .catch(err => console.error("Error SW PWA", err));
}
</script>

</body>
</html>
