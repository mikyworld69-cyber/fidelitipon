<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

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

// Obtener cupón del usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad, qr_path
    FROM cupones
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado.");
}

// Obtener casillas
$sqlCas = $conn->prepare("
    SELECT numero_casilla, marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sqlCas->bind_param("i", $cup_id);
$sqlCas->execute();
$casillas = $sqlCas->get_result();

// Estado visual
$estado = strtoupper($cupon["estado"]);
$badgeClass = "badge-activo";

if ($estado === "USADO") $badgeClass = "badge-usado";
if ($estado === "CADUCADO") $badgeClass = "badge-caducado";

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cupón</title>
<link rel="stylesheet" href="/app/app.css">

<style>
body {
    background: #f2f2f2;
    margin: 0;
    padding-bottom: 90px;
    font-family: 'Roboto', sans-serif;
}

.cupon-box {
    background: white;
    margin: 15px;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.cupon-title {
    font-size: 22px;
    font-weight: bold;
}

.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-top: 20px;
}

.casilla {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    color: white;
    font-size: 18px;
}

.casilla-marcada {
    background: #27ae60;
}

.casilla-pendiente {
    background: #bdc3c7;
}

.qr-box {
    background: #fff;
    text-align: center;
    padding: 15px;
    border-radius: 14px;
    margin-top: 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.bottom-nav {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #ddd;
    padding: 12px 0;
    display: flex;
    justify-content: space-around;
}
</style>

</head>
<body>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>
    <p><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></p>

    <div style="margin-bottom:10px;">
        <strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?>
    </div>

    <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>

    <h3 style="margin-top:25px;">Tus casillas</h3>

    <div class="casillas-grid">
        <?php while ($c = $casillas->fetch_assoc()): ?>
            <div class="casilla <?= $c["marcada"] ? 'casilla-marcada' : 'casilla-pendiente' ?>">
                <?= $c["numero_casilla"] ?>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if (!empty($cupon["qr_path"])): ?>
    <div class="qr-box">
        <h3>Tu Código QR</h3>
        <img src="/<?= $cupon["qr_path"] ?>" width="220" style="margin-top:10px;">
        <p style="font-size:13px;color:#666;">Muéstralo en el comercio para marcar tu casilla</p>
    </div>
    <?php endif; ?>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
