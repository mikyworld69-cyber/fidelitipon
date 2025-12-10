<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado → entra directo
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

// Cuando envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    // Buscar usuario
    $sql = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
    $sql->bind_param("s", $telefono);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Comparar contraseñas SIN HASH (tu DB no tiene hash)
        if ($password === $user["password"]) {

            $_SESSION["usuario_id"] = $user["id"];
            header("Location: panel_usuario.php");
            exit;

        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }

    } else {
        $mensaje = "❌ No existe ninguna cuenta con ese teléfono.";
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
body {
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.login-box {
    width: 330px;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.login-box h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #3498db;
}
.input {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #bbb;
    font-size: 16px;
    margin-bottom: 15px;
}
.btn {
    width: 100%;
    padding: 12px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    margin-top: 5px;
}
.btn:hover { background: #2980b9; }
.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 10px;
}
.link {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: #3498db;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="login-box">
    <h2>Fidelitipon</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="input" type="text" name="telefono" placeholder="Teléfono" required>
        <input class="input" type="password" name="password" placeholder="Contraseña" required>

        <button class="btn">Entrar</button>
    </form>

    <a class="link" href="register.php">Crear cuenta nueva</a>
</div>

</body>
</html>
