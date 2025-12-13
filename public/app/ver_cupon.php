<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Validar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION["usuario_id"]);

// Validar ID de cupón
if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// =====================================
// Obtener cupón del usuario
// =====================================
$sql = $conn->prepare("
    SELECT 
        c.id,
        c.titulo,
        c.descripcion,
        c.codigo,
        c.estado,
        c.fecha_caducidad,
        c.qr_path,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre,
        com.logo AS comercio_logo
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ? AND c.usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

$estado = strtoupper($cup["estado"]);

// Badge según estado
$badgeClass = "badge-activo";
if ($estado === "USADO") $badgeClass = "badge-usado";
if ($estado === "CADUCADO") $badgeClass = "badge-caducado";

// =====================================
// Obtener casillas del cupón
// =====================================
$sqlCas = $conn->prepare("
    SELECT numero_casilla, marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sqlCas->bind_param("i", $cup_id);
$sqlCas->execute();
$casillas = $sqlCas->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cupón | Fidelitipon</title>
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

.badge {
    padding: 8px 12px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
}

.badge-activo { background:#27ae60; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#c0392b; }

.cuadricula {
    display:grid;
    grid-template-columns: repeat(5, 1fr);
    gap:10px;
    margin-top:20px;
}

.celda {
    padding:12px;
    border-radius:10px;
    text-align:center;
    font-size:16px;
    background:#ecf0f1;
    border:1px solid #bdc3c7;
}

.celda.marcada {
    background:#1abc9c;
    color:white;
    font-weight:bold;
}

.qr-box img {
    width: 220px;
    display:block;
    margin:auto;
}
</style>

</head>

<body>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cup["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cup["descripcion"])) ?></div>

    <div class="info-line">
        <strong>Caduca:</strong> 
        <?= $cup["fecha_caducidad"] ? date("d/m/Y", strtotime($cup["fecha_caducidad"])) : "Sin fecha" ?>
    </div>

    <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>

    <!-- QR -->
    <div class="qr-box" style="margin-top:20px;">
        <?php if (!empty($cup["qr_path"])): ?>
            <img src="/<?= $cup["qr_path"] ?>" alt="QR del cupón">
        <?php else: ?>
            <p style="color:#888; text-align:center;">QR no disponible</p>
        <?php endif; ?>
    </div>

    <!-- Casillas -->
    <h3 style="margin-top:25px;">Progreso</h3>
    <div class="cuadricula">
        <?php foreach($casillas as $c): ?>
            <div class="celda <?= $c["marcada"] ? "marcada" : "" ?>">
                <?= $c["numero_casilla"] ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Botón usar cupón -->
    <br>
    <?php if ($estado === "ACTIVO"): ?>
        <a href="usar_cupon.php?id=<?= $cup_id ?>" class="btn-use">Usar Cupón</a>
    <?php else: ?>
        <button class="btn-use" disabled style="background:#7f8c8d;">No disponible</button>
    <?php endif; ?>

    <!-- Botón PDF -->
    <br><br>
    <a href="generar_pdf.php?id=<?= $cup_id ?>" 
       class="btn-use" 
       style="background:#8e44ad;">
       📄 Descargar PDF
    </a>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

</body>
</html>
