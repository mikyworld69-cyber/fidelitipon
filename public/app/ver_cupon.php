<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si no está logueado → fuera
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Validar cupón recibido
if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cupon_id = intval($_GET["id"]);

// Obtener cupón del usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad
    FROM cupones
    WHERE id = ? AND usuario_id = ?
    LIMIT 1
");
$sql->bind_param("i", $cupon_id, $user_id);
$sql->execute();
$data = $sql->get_result();

if ($data->num_rows === 0) {
    echo "Cupón no encontrado.";
    exit;
}

$cupon = $data->fetch_assoc();

// Badge
$badgeClass = "badge-activo";
if ($cupon["estado"] === "usado") $badgeClass = "badge-usado";
if ($cupon["estado"] === "caducado") $badgeClass = "badge-caducado";

// QR (opcional)
$qrURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode("CUPON-" . $cupon["id"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($cupon["titulo"]) ?> | Fidelitipon</title>

<!-- HEAD UNIVERSAL -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3498db">
<link rel="stylesheet" href="/app/app.css">

<style>
.cupon-container {
    padding: 20px;
}

.cupon-box {
    background: white;
    padding: 22px;
    margin-bottom: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}

.cupon-title {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 8px;
}

.badge {
    padding: 6px 12px;
    border-radius: 10px;
    font-weight: bold;
    color: white;
}

.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }

.cupon-desc {
    font-size: 15px;
    margin-bottom: 15px;
}

.qr-box {
    text-align: center;
    margin-top: 20px;
}

.btn-usar {
    display: block;
    width: 100%;
    padding: 14px;
    background: #3498db;
    color: white;
    border-radius: 12px;
    text-align: center;
    margin-top: 20px;
    font-size: 17px;
    text-decoration: none;
}

.btn-usar:hover {
    background: #2980b9;
}

.bottom-nav a.active {
    color: #3498db;
}
</style>

</head>
<body>

<div class="app-header">
    Cupón
</div>

<div class="cupon-container">

    <div class="cupon-box">
        <div class="cupon-title">
            <?= htmlspecialchars($cupon["titulo"]) ?>
        </div>

        <span class="badge <?= $badgeClass ?>">
            <?= strtoupper($cupon["estado"]) ?>
        </span>

        <p class="cupon-desc">
            <?= htmlspecialchars($cupon["descripcion"]) ?>
        </p>

        <p style="color:#7f8c8d;">
            Caduca el: <strong><?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?></strong>
        </p>
    </div>

    <div class="qr-box">
        <h3>Mostrar en comercio</h3>
        <img src="<?= $qrURL ?>" style="width:200px;">
    </div>

    <?php if ($cupon["estado"] === "activo"): ?>
        <a class="btn-usar" href="usar_cupon.php?id=<?= $cupon_id ?>">
            ✔ Marcar como usado
        </a>
    <?php endif; ?>

</div>

<!-- Navegación inferior -->
<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/sw-pwa.js");
}
</script>

</body>
</html>
