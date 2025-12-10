<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado → al panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

// LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    $sql = $conn->prepare("SELECT id, password_hash FROM usuarios WHERE telefono = ?");
    $sql->bind_param("s", $telefono);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user["password_hash"])) {
            $_SESSION["usuario_id"] = $user["id"];
            header("Location: panel_usuario.php");
            exit;
        } else {
            $mensaje = "❌ Contraseña incorrecta";
        }

    } else {
        $mensaje = "❌ El teléfono no está registrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Acceso | Fidelitipon</title>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3498db">
<link rel="icon" href="/assets/img/icon-192.png">

<!-- Estilos -->
<style>
body {
    background: #f1f5f9;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: 'Roboto', sans-serif;
}

.login-box {
    width: 90%;
    max-width: 380px;
    background: white;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #3498db;
}

input {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #bbb;
    font-size: 16px;
    margin-bottom: 14px;
}

.btn {
    width: 100%;
    background: #3498db;
    color: white;
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 17px;
    cursor: pointer;
}

.btn:hover {
    background: #2d85be;
}

.msg {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 15px;
    text-align: center;
}

a {
    text-decoration: none;
    color: #3498db;
    display: block;
    text-align: center;
    margin-top: 12px;
}
</style>

</head>
<body>

<div class="login-box">

    <h2>Acceder</h2>

    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="tel" name="telefono"
               placeholder="Teléfono" required>

        <input type="password" name="password"
               placeholder="Contraseña" required>

        <button class="btn">Entrar</button>

    </form>

    <a href="register.php">Crear cuenta nueva</a>

</div>

</body>
</html>
