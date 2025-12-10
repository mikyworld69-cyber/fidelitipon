<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Obtener datos del usuario
$sql = $conn->prepare("SELECT nombre, telefono FROM usuarios WHERE id = ?");
$sql->bind_param("i", $user_id);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();

// -------------------------
// ACTUALIZAR PERFIL
// -------------------------
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nuevo_nombre = trim($_POST["nombre"]);
    $nueva_pass   = trim($_POST["password"]);

    // Actualizar solo nombre
    if ($nuevo_nombre !== $user["nombre"]) {
        $sqlUpdate = $conn->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
        $sqlUpdate->bind_param("s", $nuevo_nombre, $user_id);
        $sqlUpdate->execute();
    }

    // Actualizar contraseña solo si se ha escrito algo
    if (!empty($nueva_pass)) {
        $pass_hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
        $sqlPass = $conn->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $sqlPass->bind_param("s", $pass_hash, $user_id);
        $sqlPass->execute();
    }

    $mensaje = "✅ Perfil actualizado correctamente";

    // refrescar datos
    header("Refresh:1");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Mi Perfil | Fidelitipon</title>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3498db">
<link rel="icon" href="/assets/img/icon-192.png">

<link rel="stylesheet" href="/app/app.css">

<style>
body {
    background: #f5f6fa;
    margin: 0;
    padding-bottom: 80px;
    font-family: 'Roboto', sans-serif;
}

.app-header {
    background: #3498db;
    padding: 18px;
    color: white;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
}

.container {
    padding: 15px;
}

.card {
    background: white;
    padding: 18px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
    margin-bottom: 15px;
}

input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 12px;
    margin-bottom: 15px;
    font-size: 16px;
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
    background: #2ecc71;
    color: white;
    padding: 12px;
    text-align: center;
    border-radius: 10px;
    margin-bottom: 10px;
}

.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
}

.bottom-nav a {
    text-align: center;
    color: #2c3e50;
    text-decoration: none;
}

.bottom-nav a.active {
    color: #3498db;
    font-weight: bold;
}
</style>

</head>
<body>

<div class="app-header">Mi Perfil</div>

<div class="container">

    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">

            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($user["nombre"]) ?>" required>

            <label>Teléfono (no editable)</label>
            <input type="text" value="<?= htmlspecialchars($user["telefono"]) ?>" disabled>

            <label>Nueva Contraseña (opcional)</label>
            <input type="password" name="password" placeholder="Escribe nueva contraseña">

            <button class="btn">Guardar Cambios</button>
        </form>
    </div>

</div>

<div class="bottom-nav">
    <a href="/app/panel_usuario.php">🏠 Inicio</a>
    <a class="active" href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/sw-pwa.js");
}
</script>

</body>
</html>
