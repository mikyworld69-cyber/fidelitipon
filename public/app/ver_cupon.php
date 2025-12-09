<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];
$cup_id = intval($_GET["id"]);

// Obtener cupón
$sql = $conn->prepare("
    SELECT c.*, com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ? AND c.usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows == 0) {
    echo "Cupón no encontrado.";
    exit;
}

$c = $res->fetch_assoc();

// Determinar estado visual
$badgeClass = "badge-activo";
if ($c["estado"] === "usado") $badgeClass = "badge-usado";
if ($c["estado"] === "caducado") $badgeClass = "badge-caducado";

// Generar URL del QR
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=" . urlencode($c["codigo"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Cupón | Fidelitipon</title>

<link rel="stylesheet" href="/public/app/app.css">

<style>
.qr-container {
    text-align: center;
    margin-top: 20px;
}

.qr-container img {
    width: 260px;
    height: 260px;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.cupon-box {
    padding: 22px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
    margin-bottom: 20px;
}

.share-btn {
    width: 100%;
    padding: 14px;
    background: #1abc9c;
    color: white;
    border-radius: 12px;
    font-size: 17px;
    border: none;
    cursor: pointer;
    margin-top: 20px;
}

.share-btn:hover {
    background: #16a085;
}
</style>

</head>
<body>

<div class="app-header">
    Detalle del Cupón
</div>

<div class="container">

    <!-- Caja Principal -->
    <div class="cupon-box">

        <h2 style="margin-bottom:10px;">
            <?= htmlspecialchars($c["titulo"]) ?>
        </h2>

        <span class="badge <?= $badgeClass ?>">
            <?= strtoupper($c["estado"]) ?>
        </span>

        <p style="margin-top:15px; color:#555;">
            <?= nl2br(htmlspecialchars($c["descripcion"])) ?>
        </p>

        <?php if ($c["comercio_nombre"]): ?>
        <p style="margin-top:10px; font-size:15px; color:#2c3e50;">
            <strong>Comercio:</strong> <?= htmlspecialchars($c["comercio_nombre"]) ?>
        </p>
        <?php endif; ?>

        <p style="margin-top:10px; color:#7f8c8d;">
            <strong>Caduca el:</strong> <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?>
        </p>

    </div>

    <!-- QR -->
    <div class="qr-container">
        <img src="<?= $qr_url ?>" alt="QR del cupón">
        <p style="margin-top:10px; color:#555;">Muestra este código en el comercio</p>
    </div>

    <!-- Botón Compartir -->
    <button class="share-btn" onclick="shareCupon()">
        📤 Compartir cupón
    </button>

    <div style="height:70px;"></div>

</div>

<!-- Navegación inferior -->
<div class="bottom-nav">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

<script>
function shareCupon() {
    if (navigator.share) {
        navigator.share({
            title: "Cupón Fidelitipon",
            text: "Aquí tienes mi cupón de <?= htmlspecialchars($c["titulo"]) ?>.",
            url: window.location.href
        });
    } else {
        alert("Tu dispositivo no permite compartir desde la app.");
    }
}
</script>

</body>
</html>
