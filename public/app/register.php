<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    // Ver si el teléfono ya existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
    $check->bind_param("s", $telefono);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $mensaje = "❌ Ese teléfono ya está registrado.";
    } else {

        // Insertar usuario
        $sql = $conn->prepare("
            INSERT INTO usuarios (nombre, telefono, password, fecha_registro)
            VALUES (?, ?, ?, NOW())
        ");
        $sql->bind_param("sss", $nombre, $telefono, $password);
        $sql->execute();

        // Login automático
        $_SESSION["usuario_id"] = $conn->insert_id;
        header("Location: panel_usuario.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro | Fidelitipon</title>

<link rel="stylesheet" href="/public/app/app.css">

<style>
body {
    background: #eef2f8;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.box {
    width: 330px;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.box h2 {
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
    background: #1abc9c;
    color: white;
    border-radius: 10px;
    border: none;
    cursor: pointer;
}
.btn:hover { background: #16a085; }
.error {
    background: #e74c3c;
    color: white;
    text-align: center;
    padding: 12px;
    border-radius: 10px;
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

<div class="box">
    <h2>Crear Cuenta</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="input" type="text" name="nombre" placeholder="Nombre" required>
        <input class="input" type="text" name="telefono" placeholder="Teléfono" required>
        <input class="input" type="password" name="password" placeholder="Contraseña" required>

        <button class="btn">Registrarse</button>
    </form>

    <a class="link" href="login.php">Ya tengo cuenta</a>
</div>

</body>
</html>
