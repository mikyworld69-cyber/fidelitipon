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

// Obtener datos del cupón
$sql = $conn->prepare("
    SELECT 
        id, titulo, descripcion, estado, fecha_caducidad, qr_path
    FROM cupones
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado o no te pertenece.");
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Cupón</title>

<link rel="stylesheet" href="/app/app.css">

<style>
body {
    background: #f5f6fa;
    margin: 0;
    padding-bottom: 90px;
    font-family: 'Roboto', sans-serif;
}
.cupon-box {
    background: white;
    padding: 22px;
    margin: 15px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.cupon-title {
    font-size: 22px;
    font-weight: bold;
}
.cupon-desc {
    margin-top: 7px;
    color: #666;
}
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 10px;
    color: white;
    font-size: 13px;
    margin-top: 8px;
}
.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }

.casillas-grid {
    margin-top: 25px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
}
.casilla {
    aspect-ratio: 1;
    border-radius: 12px;
    font-weight: bold;
    font-size: 18px;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
}
.casilla-marcada { background: #27ae60; }
.casilla-pendiente { background: #bdc3c7; }

.qr-box {
    margin-top: 30px;
    text-align: center;
    padding: 15px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}
.bottom-nav {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    background: white;
    border-top: 1px solid #ddd;
}
.bottom-nav a {
    text-decoration: none;
    color: #4a4a4a;
}
.bottom-nav a.active {
    color: #3498db;
    font-weight: bold;
}
</style>

</head>
<body>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></div>

    <div style="margin-top:10px;">
        <strong>Caduca:</strong>
        <?php if (!empty($cupon["fecha_caducidad"])): ?>
            <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?>
        <?php else: ?>
            <span style="color:#888;">Sin caducidad</span>
        <?php endif; ?>
    </div>

    <?php
        $estado = strtolower($cupon["estado"]);
        $badgeClass = $estado === "activo" ? "badge-activo" :
                      ($estado === "usado" ? "badge-usado" : "badge-caducado");
    ?>

    <span class="badge <?= $badgeClass ?>"><?= strtoupper($cupon["estado"]) ?></span>

    <h3 style="margin-top:25px;">Tus casillas</h3>

    <div class="casillas-grid">
        <?php while ($c = $casillas->fetch_assoc()): ?>
            <div class="casilla <?= $c["marcada"] ? "casilla-marcada" : "casilla-pendiente" ?>">
                <?= $c["numero_casilla"] ?>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if (!empty($cupon["qr_path"])): ?>
    <div class="qr-box">
        <h3>Presenta este QR en el comercio</h3>
        <img src="/<?= $cupon["qr_path"] ?>" width="230" alt="QR del cupón">
        <p style="font-size:13px;color:#777;">Cada escaneo marca una casilla</p>
    </div>
    <?php endif; ?>

</div>

<div class="bottom-nav">
    <a href="/app/panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
