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

// Verificar cupón pertenece al usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad, total_casillas
    FROM cupones 
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

// Obtener casillas del cupón
$qCasillas = $conn->prepare("
    SELECT numero_casilla, estado
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$qCasillas->bind_param("i", $cup_id);
$qCasillas->execute();
$casillas = $qCasillas->get_result()->fetch_all(MYSQLI_ASSOC);

// Estado visual
$estado = strtoupper($cupon["estado"]);
$badgeClass = "badge-activo";
if ($estado === "USADO") $badgeClass = "badge-usado";
if ($estado === "CADUCADO") $badgeClass = "badge-caducado";

// QR del cupón (usamos el ID o un token futuro)
$qr_url = "https://TU-DOMINIO.COM/validar/canjear.php?codigo=" . $cup_id;
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

.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr); /* 5 columnas → 10 casillas = 2 filas */
    gap: 10px;
    margin-top: 20px;
}

.casilla {
    width: 100%;
    padding-top: 100%;
    position: relative;
    border-radius: 10px;
    background: #e0e0e0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.casilla.marcada {
    background: #2ecc71;
}

.casilla span {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    font-size: 18px;
    color: white;
}

.qr-container {
    text-align: center;
    margin-top: 25px;
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

    <!-- CASILLAS DEL CUPÓN -->
    <h3 style="margin-top:20px;">Tus casillas</h3>

    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
            <div class="casilla <?= $c["estado"] == 1 ? "marcada" : "" ?>">
                <span><?= $c["numero_casilla"] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- QR PARA VALIDACIÓN -->
    <div class="qr-container">
        <h3>Tu código QR</h3>

        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($qr_url) ?>" 
             width="200" height="200">
    </div>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>
