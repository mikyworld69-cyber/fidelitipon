<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Obtener datos del usuario
$sqlUser = $conn->prepare("SELECT nombre, telefono FROM usuarios WHERE id = ?");
$sqlUser->bind_param("i", $user_id);
$sqlUser->execute();
$user = $sqlUser->get_result()->fetch_assoc();

// Obtener cupones del usuario
$sql = $conn->prepare("
    SELECT id, codigo, titulo, descripcion, estado, fecha_caducidad
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY fecha_creacion DESC
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

<!-- Estilos de la App -->
<link rel="stylesheet" href="/public/app/app.css">

<!-- Cargar badges del admin -->
<link rel="stylesheet" href="/public/admin/admin.css">

<style>
.container {
    padding: 20px;
    margin-bottom: 80px;
}
</style>
</head>
<body>

<div class="app-header">
    Mis Cupones
</div>

<div class="container">

    <!-- Información del usuario -->
    <div class="card">
        <h3>Hola, <?= htmlspecialchars($user["nombre"] ?: "Usuario") ?></h3>
        <p style="color:#7f8c8d;">Tel: <?= htmlspecialchars($user["telefono"]) ?></p>
    </div>

    <h2 style="margin:20px 0 10px 0;">Cupones Disponibles</h2>

    <?php if ($cupones->num_rows == 0): ?>
        <div class="card" style="text-align:center;">
            <p>No tienes cupones por ahora.</p>
        </div>
    <?php endif; ?>

    <?php while ($c = $cupones->fetch_assoc()): ?>

        <?php
        $badgeClass = "badge-activo";
        if ($c["estado"] === "usado") $badgeClass = "badge-usado";
        if ($c["estado"] === "caducado") $badgeClass = "badge-caducado";
        ?>

        <div class="cupon-card" onclick="location.href='ver_cupon.php?id=<?= $c['id'] ?>'">
            <div class="cupon-title">
                <?= htmlspecialchars($c["titulo"]) ?>
            </div>

            <div class="cupon-desc">
                <?= htmlspecialchars($c["descripcion"]) ?>
            </div>

            <div class="caduca">
                Caduca: <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?>
            </div>

            <span class="badge <?= $badgeClass ?>">
                <?= strtoupper($c["estado"]) ?>
            </span>
        </div>

    <?php endwhile; ?>

</div>

<!-- Navegación inferior -->
<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

<!-- Registrar Notificaciones Push -->
<script src="/push/notificaciones.js"></script>

<!-- Botón PWA -->
<button class="btn" id="btnInstalar" style="margin:20px; display:none;">
    📲 Instalar App
</button>

<script>
let deferredPrompt;

window.addEventListener("beforeinstallprompt", e => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById("btnInstalar").style.display = "block";
});

document.getElementById("btnInstalar").addEventListener("click", async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
    }
});
</script>

</body>
</html>
