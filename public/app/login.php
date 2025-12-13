<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$mensaje = "";

// Si ya está logueado
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

// Login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    $sql = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
    $sql->bind_param("s", $telefono);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["usuario_id"] = $user["id"];
            header("Location: panel_usuario.php");
            exit;
        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }
    } else {
        $mensaje = "❌ El número no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | Fidelitipon</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    background: #f5f6fa;
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-box {
    width: 90%;
    max-width: 360px;
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.10);
    text-align: center;
}

h2 {
    margin-bottom: 20px;
    color: #3498db;
    font-size: 22px;
}

.input {
    width: 100%;
    padding: 10px;
    font-size: 15px;
    margin: 10px 0;
    border-radius: 10px;
    border: 1px solid #ccc;
}

.btn {
    width: 100%;
    padding: 12px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    margin-top: 10px;
}

.btn:hover {
    background: #2980b9;
}

.error {
    background: #e74c3c;
    padding: 10px;
    color: white;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}
</style>

</head>
<body>

<div class="login-box">
    <h2>Acceso Usuarios</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="telefono" class="input" placeholder="Teléfono" required>
        <input type="password" name="password" class="input" placeholder="Contraseña" required>

        <button class="btn">Entrar</button>
    </form>
</div>

</body>
</html>
