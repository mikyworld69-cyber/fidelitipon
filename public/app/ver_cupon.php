<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Cupón no especificado.";
    exit;
}

$cup_id = intval($_GET["id"]);

// ================================
// OBTENER CUPÓN
// ================================
$sql = $conn->prepare("
    SELECT c.*, 
           u.nombre AS usuario_nombre, 
           u.telefono AS usuario_telefono,
           com.nombre AS comercio_nombre,
           com.logo AS comercio_logo
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    echo "Cupón no encontrado.";
    exit;
}

// ================================
// OBTENER CASILLAS
// ================================
$qCas = $conn->prepare("
    SELECT numero_casilla, estado, fecha_marcado
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$qCas->bind_param("i", $cup_id);
$qCas->execute();
$casillas = $qCas->get_result()->fetch_all(MYSQLI_ASSOC);

// progreso
$total = $cup["total_casillas"];
$usadas = 0;
foreach ($casillas as $c) { if ($c["estado"] == 1) $usadas++; }

$porcentaje = round(($usadas / $total) * 100);

// logo comercio
$logo = $cup["comercio_logo"] ?: "/img/default_logo.png";

// QR
$qr_url = "https://TU-DOMINIO.COM/validar/validar_html.php?codigo=" . $cup_id;
?>

<style>
.premium-box {
    background: white;
    width: 90%;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    animation: fadeIn 0.7s ease;
    text-align: center;
}

@keyframes fadeIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }

.logo-comercio {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-bottom: 15px;
}

.cupon-title {
    font-size: 24px;
    font-weight: bold;
}

.cupon-desc {
    color: #666;
    font-size: 15px;
    margin-bottom: 15px;
}

.badge {
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 13px;
    color: white;
}
.badge-activo { background: #2ecc71; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #e74c3c; }

/* DONUT */
.donut {
    width: 160px;
    height: 160px;
    margin: 20px auto;
}
.donut-text {
    font-size: 20px;
    fill: #333;
    font-weight: bold;
}

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
    border-radius: 12px;
    background: #ddd;
    position: relative;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
}

.casilla.marcada {
    background: #2ecc71;
}

.casilla span {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    font-size: 18px;
    color: white;
    font-weight: bold;
}

/* QR */
.qr-box img {
    width: 220px;
    height: 220px;
    border-radius: 18px;
    margin-top: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* ACCIONES ADMIN */
.admin-actions {
    margin-top: 25px;
}

.admin-actions a {
    display: inline-block;
    padding: 10px 16px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    margin: 5px;
    text-decoration: none;
    font-size: 14px;
}

.admin-actions a:hover {
    background: #2a7abc;
}
</style>

<div class="premium-box">

    <img src="<?= $logo ?>" class="logo-comercio">

    <div class="cupon-title"><?= htmlspecialchars($cup["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cup["descripcion"])) ?></div>

    <?php
        $badgeClass =
            ($cup["estado"] === "usado" ? "badge-usado" :
            ($cup["estado"] === "caducado" ? "badge-caducado" : "badge-activo"));
    ?>
    <span class="badge <?= $badgeClass ?>"><?= strtoupper($cup["estado"]) ?></span>

    <p><strong>Usuario:</strong> <?= $cup["usuario_nombre"] ?: "—" ?></p>
    <p><strong>Comercio:</strong> <?= htmlspecialchars($cup["comercio_nombre"]) ?></p>
    <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cup["fecha_caducidad"])) ?></p>

    <!-- DONUT -->
    <svg class="donut" viewBox="0 0 36 36">
        <path 
            d="M18 2.0845
               a 15.9155 15.9155 0 0 1 0 31.831
               a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none"
            stroke="#eee"
            stroke-width="3"
        ></path>

        <path 
            d="M18 2.0845
               a 15.9155 15.9155 0 0 1 0 31.831
               a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none"
            stroke="#3498db"
            stroke-width="3"
            stroke-dasharray="<?= $porcentaje ?>, 100"
        ></path>

        <text x="18" y="20.35" class="donut-text" text-anchor="middle">
            <?= $porcentaje ?>%
        </text>
    </svg>

    <!-- CASILLAS -->
    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
            <div class="casilla <?= $c["estado"] ? 'marcada' : '' ?>">
                <span><?= $c["numero_casilla"] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- QR -->
    <div class="qr-box">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($qr_url) ?>">
    </div>

    <!-- BOTONES ADMIN -->
    <div class="admin-actions">
        <a href="editar_cupon.php?id=<?= $cup_id ?>">✏️ Editar</a>
        <a href="eliminar_cupon.php?id=<?= $cup_id ?>" onclick="return confirm('¿Eliminar este cupón?');">🗑️ Eliminar</a>
        <a href="cupones.php">⬅️ Volver</a>
    </div>

</div>

<?php include "_footer.php"; ?>
