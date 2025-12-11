<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// VALIDACIÓN DE SESIÓN CORRECTA
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// OBTENER DATOS DEL USUARIO
$sql = $conn->prepare("SELECT nombre, telefono FROM usuarios WHERE id = ?");
$sql->bind_param("i", $user_id);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();

$mensaje = "";

// ACTUALIZAR PERFIL
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);

    if ($nombre === "" || $telefono === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        $up = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE id = ?");
        $up->bind_param("ssi", $nombre, $telefono, $user_id);
        $up->execute();

        $mensaje = "Cambios guardados correctamente.";
        $user["nombre"] = $nombre;
        $user["telefono"] = $telefono;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Mi Perfil</title>

<link rel="stylesheet" href="/app/app.css">

<style>
.perfil-box {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin: 15px;
}
.label {
    font-weight: bold;
    margin-bottom: 4px;
    font-size: 14px;
}
input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}
.btn-save {
    width: 100%;
    padding: 14px;
    background: #3498db;
    border-radius: 12px;
    color: white;
    text-align: center;
    display: block;
    margin-top: 10px;
    font-size: 17px;
}
.btn-save:hover { background: #2980b9; }
.msg {
    background: #1abc9c;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 10px;
    color: white;
}
.btn-danger {
    width: 100%;
    padding: 14px;
    background: #e74c3c;
    border-radius: 12px;
    color: white;
    display: block;
    text-align: center;
    margin-top: 15px;
    font-size: 17px;
}
.bottom-nav {
    position: fixed; bottom: 0; left: 0; width: 100%;
    background: white; border-top: 1px solid #ddd;
    display: flex; justify-content: space-around;
    padding: 12px 0; box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
}
.bottom-nav a {
    text-align: center; color: #2c3e50; text-decoration: none;
}
.bottom-nav a.active {
    color: #3498db; font-weight: bold;
}
</style>
</head>

<body>

<div class="app-header">Mi Perfil</div>

<div class="perfil-box">

    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">

        <label class="label">Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($user["nombre"]) ?>">

        <label class="label">Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($user["telefono"]) ?>">

        <button class="btn-save">Guardar Cambios</button>
    </form>

    <a class="btn-save" href="cambiar_password.php">🔐 Cambiar contraseña</a>

    <a class="btn-danger" href="/logout.php">Cerrar sesión</a>

</div>

<!-- BOTTOM NAV -->
<div class="bottom-nav">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="perfil.php" class="active">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
