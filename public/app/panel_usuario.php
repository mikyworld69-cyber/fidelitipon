<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Obtener usuario
$sqlUser = $conn->prepare("SELECT nombre, telefono FROM usuarios WHERE id = ?");
$sqlUser->bind_param("i", $user_id);
$sqlUser->execute();
$user = $sqlUser->get_result()->fetch_assoc();

// Obtener cupones
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$sql->bind_param("i", $user_id);
$sql->execute();
$cupones = $sql->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Mis Cupones | Fidelitipon</title>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3498db">

<!-- ICONOS -->
<link rel="icon" href="/assets/img/icon-192.png">

<!-- APP CSS -->
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

.cupon-card {
    padding: 18px;
    border-radius: 14px;
    background: white;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
    cursor: pointer;
}

.cupon-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 8px;
}

.badge {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 12px;
    color: #fff;
}

.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }

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

<div class="app-header">Mis Cupones</div>

<div class="container">

    <div class="card">
        <h3>Hola, <?= htmlspecialchars($user["nombre"]) ?></h3>
        <p style="color:#7f8c8d;">📱 Tel: <?= htmlspecialchars($user["telefono"]) ?></p>
    </div>

    <h2 style="margin-bottom:10px;">Cupones Disponibles</h2>

    <?php if ($cupones->num_rows === 0): ?>
        <div class="card" style="text-align:center;">
            <p>No tienes cupones todavía.</p>
        </div>
    <?php endif; ?>

    <?php while ($c = $cupones->fetch_assoc()): 
        $estadoClass = "badge-activo";
        if ($c["estado"] === "usado") $estadoClass = "badge-usado";
        if ($c["estado"] === "caducado") $estadoClass = "badge-caducado";
    ?>
        <div class="cupon-card" onclick="location.href='ver_cupon.php?id=<?= $c['id'] ?>'">
            <div class="cupon-title"><?= htmlspecialchars($c["titulo"]) ?></div>
            <div><?= htmlspecialchars($c["descripcion"]) ?></div>
            <div style="margin-top:10px; color:#7f8c8d;">
                Caduca: <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?>
            </div>
            <span class="badge <?= $estadoClass ?>"><?= strtoupper($c["estado"]) ?></span>
        </div>
    <?php endwhile; ?>

</div>

<!-- NAVBAR INFERIOR -->
<div class="bottom-nav">
    <a class="active" href="/app/panel_usuario.php">🏠 Inicio</a>
    <a href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

<!-- SERVICE WORKER -->
<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/sw-pwa.js");
}
</script>

<!-- Suscripción push -->
<script src="/push/notificaciones.js"></script>

</body>
</html>
