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
    SELECT id, titulo, descripcion, estado, fecha_caducidad, total_casillas, comercio_id
    FROM cupones 
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

$estado = strtolower($cupon["estado"]);

// Obtener casillas
$qCasillas = $conn->prepare("
    SELECT numero_casilla, estado
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$qCasillas->bind_param("i", $cup_id);
$qCasillas->execute();
$casillas = $qCasillas->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener logo del comercio
$qCom = $conn->prepare("
    SELECT nombre, logo 
    FROM comercios 
    WHERE id = ?
");
$qCom->bind_param("i", $cupon["comercio_id"]);
$qCom->execute();
$comercio = $qCom->get_result()->fetch_assoc();

$logo = (!empty($comercio["logo"])) ? $comercio["logo"] : "/img/default_logo.png";

// URL QR
$qr_url = "https://TU-DOMINIO.COM/validar/validar_html.php?codigo=" . $cup_id;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Cupón Premium</title>

<style>

body {
    margin: 0;
    background: #f0f2f5;
    font-family: 'Roboto', sans-serif;
}

/* CABECERA */
.app-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    padding: 28px 20px;
    color: white;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
}

/* TARJETA PREMIUM */
.cupon-box {
    background: white;
    width: 90%;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    animation: fadeIn 0.7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(25px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* LOGO COMERCIO */
.logo-comercio {
    width: 110px;
    height: 110px;
    object-fit: contain;
    margin: 0 auto 15px;
    display: block;
}

/* TÍTULO */
.cupon-title {
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 5px;
}

/* DESCRIPCIÓN */
.cupon-desc {
    text-align: center;
    font-size: 15px;
    color: #666;
    margin-bottom: 15px;
}

/* ESTADO */
.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 15px;
    color: white;
}

.badge-activo { background: #2ecc71; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #e74c3c; }

/* CASILLAS */
.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-top: 20px;
}

.casilla {
    width: 100%;
    padding-top: 100%;
    background: #ddd;
    border-radius: 12px;
    position: relative;
    box-shadow: inset 0 0 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.casilla.marcada {
    background: #2ecc71;
}

.casilla span {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    font-size: 18px;
    font-weight: bold;
    color: white;
}

/* QR */
.qr-container {
    margin-top: 30px;
    text-align: center;
}

.qr-container img {
    width: 200px;
    height: 200px;
    border-radius: 16px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* FOOTER */
.bottom-nav {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
}

.bottom-nav a {
    text-align: center;
    font-size: 16px;
    text-decoration: none;
    color: #444;
}

.bottom-nav a.active {
    color: #2575fc;
    font-weight: bold;
}

</style>

</head>

<body>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <img src="<?= $logo ?>" class="logo-comercio">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></div>

    <?php
        $badgeClass =
            ($estado === "usado" ? "badge-usado" :
            ($estado === "caducado" ? "badge-caducado" : "badge-activo"));
    ?>
    <span class="badge <?= $badgeClass ?>"><?= strtoupper($cupon["estado"]) ?></span>

    <div style="text-align:center; color:#777; font-size:14px;">
        Caduca el: <strong><?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?></strong>
    </div>

    <!-- CASILLAS GRANDES PREMIUM -->
    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
            <div class="casilla <?= $c["estado"] == 1 ? 'marcada' : '' ?>">
                <span><?= $c["numero_casilla"] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- QR PREMIUM -->
    <div class="qr-container">
        <h3 style="margin-bottom:12px;">Tu Código QR</h3>

        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($qr_url) ?>">
    </div>

</div>

<!-- MENÚ INFERIOR -->
<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

</body>
</html>
