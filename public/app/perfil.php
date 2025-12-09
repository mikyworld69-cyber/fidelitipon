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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil | Fidelitipon</title>

<link rel="stylesheet" href="/public/app/app.css">

<style>
.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    font-size: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 15px auto;
}
.profile-item {
    padding: 15px;
    background: white;
    border-radius: 12px;
    font-size: 17px;
    margin-bottom: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
.logout-btn {
    width: 100%;
    padding: 14px;
    margin-top: 20px;
    background: #e74c3c;
    color: white;
    border-radius: 12px;
    border: none;
    font-size: 17px;
}
.logout-btn:hover {
    background: #c0392b;
}
</style>

</head>
<body>

<div class="app-header">
    Mi Perfil
</div>

<div class="container">

    <div class="avatar">
        <?= strtoupper(substr($user["nombre"] ?: "U", 0, 1)) ?>
    </div>

    <div class="profile-item">
        <strong>Nombre:</strong><br>
        <?= htmlspecialchars($user["nombre"] ?: "No establecido") ?>
    </div>

    <div class="profile-item">
        <strong>Teléfono:</strong><br>
        <?= htmlspecialchars($user["telefono"]) ?>
    </div>

    <button class="logout-btn" onclick="location.href='../logout.php'">
        🚪 Cerrar sesión
    </button>

</div>

<!-- Barra inferior -->
<div class="bottom-nav">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="perfil.php" class="active">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

</body>
</html>
