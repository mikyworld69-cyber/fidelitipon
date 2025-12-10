<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya tiene sesión → panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

// PROCESO LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

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
            $mensaje = "❌ Contraseña incorrecta";
        }

    } else {
        $mensaje = "❌ No existe una cuenta con ese teléfono";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Entrar | Fidelitipon</title>

<style>
body {
    margin: 0;
    padding: 0;
    background: #eef2f7;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Caja */
.login-box {
    width: 330px;
    background: white;
    padding: 30px 25px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    text-align: center;
}

/* Icono */
.icon {
    font-size: 60px;
    margin-bottom: 10px;
    color: #3498db;
}

h2 {
    margin-bottom: 25px;
    font-size: 24px;
    color: #2c3e50;
}

/* Inputs */
.input {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    font-size: 15px;
}

/* Botón */
.btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 12px;
    background: #3498db;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-top: 5px;
}

.btn:hover {
    background: #2980b9;
}

/* Error */
.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 15px;
    font-size: 14px;
}

/* Enlace registro */
.register-link {
    margin-top: 12px;
    font-size: 14px;
}

.register-link a {
    color: #3498db;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="login-box">
    <div class="icon">🔐</div>
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
        ¿No tienes cuenta? <a href="register.php">Regístrate</a>
    </div>
</div>

</body>
</html>
