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

<link rel="stylesheet" href="/public/app/app.css">

<style>
/* Barra inferior tipo app */
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
    font-size: 15px;
    text-decoration: none;
}

.bottom-nav a.active {
    color: #3498db;
    font-weight: bold;
}

.cupon-card {
    padding: 18px;
    border-radius: 14px;
    background: white;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
    cursor: pointer;
}

.cupon-card:hover {
    background: #f9f9f9;
    transform: scale(1.01);
    transition: 0.15s;
}

.cupon-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 8px;
}

.cupon-desc {
    font-size: 14px;
    color: #555;
}

.caduca {
    margin-top: 10px;
    font-size: 13px;
    color: #7f8c8d;
}

/* Header */
.app-header {
    background: #3498db;
    padding: 18px;
    color: white;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
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

    <!-- Lista de cupones -->
    <h2 style="margin-bottom:10px;">Cupones Disponibles</h2>

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

    <div style="height:70px;"></div> <!-- espacio para navbar -->

</div>

<!-- Navegación inferior -->
<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

<!-- Suscripción Push -->
<script>
if ("serviceWorker" in navigator && "PushManager" in window) {

    navigator.serviceWorker.register("../push/sw.js")
        .then(swReg => {
            return swReg.pushManager.getSubscription()
                .then(sub => {
                    if (sub) return sub;

                    return swReg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: "BA4M737w3LmyAiXYmDwOihcwEflN-o9Axjz7wBBlo7ICzjhURi6EoqRpOA9phRgpaKTOuKzNlNCl2n8y2M632UI"
                    });
                })
                .then(sub => {
                    fetch("../push/push_subscribe.php", {
                        method: "POST",
                        body: JSON.stringify(sub),
                        headers: { "Content-Type": "application/json" }
                    });
                });
        });
}
</script>

<button class="btn" id="btnInstalar" style="margin-top:15px;">
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
        const outcome = await deferredPrompt.userChoice;
        if (outcome.outcome === "accepted") {
            console.log("PWA instalada");
        }
        deferredPrompt = null;
    }
});
</script>


</body>
</html>
