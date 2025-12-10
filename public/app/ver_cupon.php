<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

if (!isset($_GET["id"])) {
    die("Cupón no especificado.");
}

$cupon_id = intval($_GET["id"]);

// Obtener cupón
$sql = $conn->prepare("
    SELECT c.*, com.nombre AS comercio_nombre 
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.id = ? AND c.usuario_id = ?
    LIMIT 1
");
$sql->bind_param("ii", $cupon_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado.");
}

// Determinar estado visual
$estado = $cupon["estado"];
$badge = "badge-activo";
if ($estado === "usado") $badge = "badge-usado";
if ($estado === "caducado") $badge = "badge-caducado";

// Detectar caducidad automáticamente
$hoy = date("Y-m-d");
if ($cupon["fecha_caducidad"] < $hoy && $estado === "activo") {
    // marcar caducado en BD
    $upd = $conn->prepare("UPDATE cupones SET estado='caducado' WHERE id=?");
    $upd->bind_param("i", $cupon_id);
    $upd->execute();
    $estado = "caducado";
    $badge = "badge-caducado";
}

// URL QR → apunta al validador
$qrURL = "https://" . $_SERVER['HTTP_HOST'] . "/validar_cupon.php?id=" . $cupon_id;

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ver Cupón | Fidelitipon</title>
<link rel="stylesheet" href="/app/app.css">

<style>
.header {
    background: #3498db;
    color: white;
    padding: 16px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
}

.card {
    padding: 18px;
    background: white;
    margin: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.qr-box {
    text-align: center;
    margin-top: 15px;
}

.qr-box img {
    width: 220px;
    height: 220px;
}

.btn-volver {
    display: block;
    margin: 25px auto;
    width: 90%;
    text-align: center;
    padding: 12px;
    border-radius: 12px;
    background: #34495e;
    color: white;
    text-decoration: none;
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
</style>

</head>
<body>

<div class="header">Cupón</div>

<div class="card">

    <h2><?= htmlspecialchars($cupon["titulo"]) ?></h2>
    <p style="color:#555;"><?= htmlspecialchars($cupon["descripcion"]) ?></p>

    <p><strong>Comercio:</strong> <?= htmlspecialchars($cupon["comercio_nombre"] ?: "—") ?></p>

    <p><strong>Código:</strong> <?= htmlspecialchars($cupon["codigo"]) ?></p>

    <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?></p>

    <p>
        <strong>Estado:</strong>
        <span class="badge <?= $badge ?>">
            <?= strtoupper($estado) ?>
        </span>
    </p>

    <?php if ($estado === "activo"): ?>
        <div class="qr-box">
            <h3>Mostrar al comercio</h3>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=<?= urlencode($qrURL) ?>" alt="QR">
            <p style="margin-top:10px;">El comercio escaneará este código para validar el cupón.</p>
        </div>
    <?php elseif ($estado === "usado"): ?>
        <p style="color:#7f8c8d; font-weight:bold; margin-top:15px;">✔ Este cupón ya fue usado.</p>
    <?php else: ?>
        <p style="color:#c0392b; font-weight:bold; margin-top:15px;">⚠ Cupón caducado.</p>
    <?php endif; ?>

</div>

<a href="panel_usuario.php" class="btn-volver">⬅ Volver</a>

</body>
</html>
