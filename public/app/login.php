<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    // Buscar usuario
    $sql = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
    $sql->bind_param("s", $telefono);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $u = $res->fetch_assoc();

        if (password_verify($password, $u["password"])) {
            $_SESSION["usuario_id"] = $u["id"];
            header("Location: panel_usuario.php");
            exit;
        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }
    } else {
        $mensaje = "❌ Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | Fidelitipon</title>
<link rel="stylesheet" href="app.css">

<style>
body {
    background: #f1f1f1;
    font-family: Arial;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.login-box {
    background: white;
    padding: 30px;
    width: 320px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
.input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #bbb;
}
.btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: #3498db;
    color: white;
    font-size: 16px;
    cursor: pointer;
}
.btn:hover {
    background: #2980b9;
}
.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    text-align: center;
}
.register-link {
    text-align: center;
    margin-top: 10px;
}
</style>

</head>
<body>

<div class="login-box">
    <h2>Iniciar Sesión</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="input" type="text" name="telefono" placeholder="Teléfono" required>
        <input class="input" type="password" name="password" placeholder="Contraseña" required>
        <button class="btn">Entrar</button>
    </form>

    <div class="register-link">
        <a href="register.php">Crear cuenta</a>
    </div>
</div>

</body>
</html>
