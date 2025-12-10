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

// Obtener cupones (sin fecha_creacion)
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY fecha_caducidad DESC
");
$sql->bind_param("i", $user_id);
$sql->execute();
$cupones = $sql->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Cupones | Fidelitipon</title>

<link rel="stylesheet" href="/app/app.css">

<style>
/* Header */
.app-header {
    background: #3498db;
    padding: 18px;
    color: white;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
}

/* Carta de cupones */
.cupon-card {
    padding: 18px;
    border-radius: 14px;
    background: white;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
    cursor: pointer;
}
.cupon-card:hover {
    background: #f3f7fa;
    transform: scale(1.01);
}

.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: bold;
    color: white;
}

.badge-activo { background:#27ae60; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#c0392b; }

.bottom-nav {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #ccc;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    font-size: 16px;
}

.bottom-nav a {
    text-decoration: none;
    color: #555;
    text-align: center;
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

    <div class="card" style="margin-bottom:20px;">
        <h3>Hola, <?= htmlspecialchars($user["nombre"] ?: "Usuario") ?></h3>
        <p style="color:#7f8c8d;">Tel: <?= htmlspecialchars($user["telefono"]) ?></p>
    </div>

    <h2>Cupones Disponibles</h2>

    <?php if ($cupones->num_rows == 0): ?>
        <div class="card" style="text-align:center;">
            No tienes cupones todavía.
        </div>
    <?php endif; ?>

    <?php while ($c = $cupones->fetch_assoc()): ?>

        <?php
        $badge = "badge-activo";
        if ($c["estado"] === "usado") $badge = "badge-usado";
        if ($c["estado"] === "caducado") $badge = "badge-caducado";
        ?>

        <div class="cupon-card" onclick="location.href='ver_cupon.php?id=<?= $c['id'] ?>'">
            <div style="font-size:18px; font-weight:bold;">
                <?= htmlspecialchars($c["titulo"]) ?>
            </div>

            <div style="margin-top:6px; color:#666;">
                <?= htmlspecialchars($c["descripcion"]) ?>
            </div>

            <div style="margin-top:10px; color:#888;">
                Caduca: <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?>
            </div>

            <span class="badge <?= $badge ?>">
                <?= strtoupper($c["estado"]) ?>
            </span>
        </div>

    <?php endwhile; ?>

    <div style="height:80px;"></div>
</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="/app/logout.php">🚪 Salir</a>
</div>

<script src="/push/notificaciones.js"></script>

    <button id="btnInstalar" class="btn" style="display:none; margin:25px auto; width:90%;">
    📲 Instalar Fidelitipon
</button>

<script>
let deferredPrompt = null;

window.addEventListener("beforeinstallprompt", e => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById("btnInstalar").style.display = "block";
});

document.getElementById("btnInstalar").addEventListener("click", async () => {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    const outcome = await deferredPrompt.userChoice;

    if (outcome.outcome === "accepted") {
        console.log("PWA instalada");
    }
    deferredPrompt = null;
});
</script>


</body>
</html>
