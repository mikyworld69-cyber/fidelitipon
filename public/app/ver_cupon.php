<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include __DIR__ . '/../includes/head_app.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];
if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// Verificar que el cupón pertenece al usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad 
    FROM cupones 
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

$estado = strtoupper($cupon["estado"]);

$badgeClass = "badge-activo";
if ($estado === "USADO") $badgeClass = "badge-usado";
if ($estado === "CADUCADO") $badgeClass = "badge-caducado";

?>
<link rel="stylesheet" href="/app/app.css">

<style>
.cupon-box {
    background: white;
    margin: 20px;
    padding: 22px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
}

.cupon-title {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
}

.cupon-desc {
    font-size: 15px;
    color: #555;
    margin-bottom: 15px;
}

.info-line {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.btn-use {
    width: 100%;
    padding: 15px;
    background: #1abc9c;
    color: white;
    border-radius: 12px;
    font-size: 18px;
    text-align: center;
    display: block;
    border: none;
}

.btn-use:hover {
    background: #16a085;
}

.disabled-btn {
    background: #7f8c8d !important;
}
</style>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></div>

    <div class="info-line">
        <strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?>
    </div>

    <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>

    <br><br>

    <?php if ($estado === "ACTIVO"): ?>
        <a href="usar_cupon.php?id=<?= $cup_id ?>" class="btn-use">Usar Cupón</a>
    <?php else: ?>
        <button class="btn-use disabled-btn" disabled>Cupón no disponible</button>
    <?php endif; ?>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>
