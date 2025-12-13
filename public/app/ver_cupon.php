<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include __DIR__ . '/../includes/head_app.php';

// Validar login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Validar ID cupón
if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// =======================
// OBTENER CUPÓN
// =======================

$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad, qr_path
    FROM cupones
    WHERE id = ? AND usuario_id = ?
    LIMIT 1
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado o no pertenece a tu cuenta.");
}

// =======================
// OBTENER CASILLAS
// =======================

$sql = $conn->prepare("
    SELECT numero_casilla, marcada, fecha_marcada, comercio_id
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$casillas = $sql->get_result()->fetch_all(MYSQLI_ASSOC);

// =======================
// FUNCIONES AUX
// =======================

function fechaBonita($f) {
    if (!$f) return "";
    return date("d/m/Y H:i", strtotime($f));
}

$estadoClass = [
    "activo" => "badge-activo",
    "usado" => "badge-usado",
    "caducado" => "badge-caducado"
][$cup["estado"]];
?>
<link rel="stylesheet" href="/app/app.css">

<style>
.cupon-box {
    background: white;
    margin: 20px;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.cupon-title {
    font-size: 24px;
    font-weight: bold;
}
.cupon-desc {
    margin-top: 10px;
    font-size: 15px;
    color: #555;
}
.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-top: 20px;
}
.casilla {
    border-radius: 12px;
    padding: 18px;
    font-size: 18px;
    text-align: center;
    background: #ecf0f1;
    color: #2c3e50;
    font-weight: bold;
    border: 2px solid #bdc3c7;
}
.casilla.marcada {
    background: #27ae60;
    color: white;
    border-color: #1e8449;
}
.qr-box {
    text-align: center;
    margin-top: 20px;
}
.qr-box img {
    width: 260px;
    max-width: 100%;
}
.completado-box {
    padding: 15px;
    margin-top: 15px;
    text-align: center;
    border-radius: 14px;
    background: #2ecc71;
    color: white;
    font-size: 18px;
    font-weight: bold;
}
</style>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cup["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cup["descripcion"])) ?></div>

    <div style="margin-top:10px; color:#555;">
        <strong>Caduca:</strong>
        <?= date("d/m/Y", strtotime($cup["fecha_caducidad"])) ?>
    </div>

    <span class="badge <?= $estadoClass ?>" style="margin-top:10px; display:inline-block;">
        <?= strtoupper($cup["estado"]) ?>
    </span>

    <!-- QR DEL CUPÓN -->
    <?php if ($cup["qr_path"]): ?>
    <div class="qr-box">
        <img src="/<?= $cup["qr_path"] ?>" alt="QR del cupón">
    </div>
    <?php endif; ?>

    <!-- CASILLAS -->
    <h3 style="margin-top:20px;">Progreso</h3>

    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
            <div class="casilla <?= $c["marcada"] ? 'marcada' : '' ?>">
                <?= $c["numero_casilla"] ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- COMPLETADO -->
    <?php if ($cup["estado"] === "usado"): ?>
    <div class="completado-box">
        🎉 ¡Cupón completado! Presenta esta pantalla en el comercio.
    </div>
    <?php endif; ?>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>
