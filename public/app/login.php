<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado, enviamos al panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

if (isset($_POST["login"])) {

    $telefono  = trim($_POST["telefono"]);
    $password  = trim($_POST["password"]);

    if ($telefono === "" || $password === "") {
        $mensaje = "Rellena todos los campos.";
    } else {

        $sql = $conn->prepare("SELECT id, password_hash FROM usuarios WHERE telefono = ?");
        $sql->bind_param("s", $telefono);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows == 0) {
            $mensaje = "Teléfono no encontrado.";
        } else {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password_hash"])) {
                $_SESSION["usuario_id"] = $user["id"];
                header("Location: panel_usuario.php");
                exit;
            } else {
                $mensaje = "Contraseña incorrecta.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar Sesión | Fidelitipon</title>

<link rel="stylesheet" href="/public/app/app.css">

<style>
.login-container {
    max-width: 420px;
    margin: 80px auto;
}
a { color: #3498db; }
</style>

</head>
<body>

<div class="container login-container">

    <div class="card">
        <h2 style="text-align:center; margin-bottom:20px;">Iniciar Sesión</h2>

        <?php if ($mensaje): ?>
            <div class="msg" style="background:#c0392b; color:white;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="600123456" required>

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Tu contraseña" required>

            <button class="btn" type="submit" name="login">Entrar</button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            ¿No tienes cuenta?
            <a href="register.php">Crear cuenta</a>
        </p>

    </div>

</div>

</body>
</html>
