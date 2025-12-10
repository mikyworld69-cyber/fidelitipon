<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado → panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";
$ok = false;

// REGISTRO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    // Validar duplicado
    $sqlCheck = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
    $sqlCheck->bind_param("s", $telefono);
    $sqlCheck->execute();
    $exists = $sqlCheck->get_result();

    if ($exists->num_rows > 0) {
        $mensaje = "⚠️ Este teléfono ya está registrado.";
    } else {
        // Insertar usuario nuevo
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sqlInsert = $conn->prepare("
            INSERT INTO usuarios (nombre, telefono, password_hash, fecha_registro)
            VALUES (?, ?, ?, NOW())
        ");
        $sqlInsert->bind_param("sss", $nombre, $telefono, $hash);

        if ($sqlInsert->execute()) {
            $ok = true;
            $mensaje = "✅ Cuenta creada correctamente. Redirigiendo...";
            header("refresh:2;url=login.php");
        } else {
            $mensaje = "❌ Error al crear la cuenta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Crear Cuenta | Fidelitipon</title>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3498db">
<link rel="icon" href="/assets/img/icon-192.png">

<style>
body {
    background: #eef2f5;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: 'Roboto', sans-serif;
}

.register-box {
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

.msg.ok {
    background: #2ecc71;
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

<div class="register-box">

    <h2>Crear Cuenta</h2>

    <?php if ($mensaje): ?>
        <div class="msg <?= $ok ? 'ok' : '' ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="nombre"
               placeholder="Tu nombre"
               required>

        <input type="tel" name="telefono"
               placeholder="Teléfono"
               required>

        <input type="password" name="password"
               placeholder="Contraseña"
               required>

        <button class="btn">Crear Cuenta</button>

    </form>

    <a href="login.php">Ya tengo cuenta</a>

</div>

</body>
</html>
