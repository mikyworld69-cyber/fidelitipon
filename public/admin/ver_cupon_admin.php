<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar ID
if (!isset($_GET["id"])) {
    die("ID de cupón no recibido");
}

$cup_id = intval($_GET["id"]);

// =====================================
// 1) OBTENER DATOS DEL CUPÓN
// =====================================

$sql = $conn->prepare("
    SELECT 
        c.id,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_caducidad,
        c.qr_path,
        u.nombre AS usuario_nombre,
        u.telefono AS usuario_telefono,
        com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.id = ?
    LIMIT 1
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado");
}

// =====================================
// 2) OBTENER CASILLAS
// =====================================

$sql = $conn->prepare("
    SELECT numero_casilla, marcada, fecha_marcada, comercio_id
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$casillas = $sql->get_result()->fetch_all(MYSQLI_ASSOC);

// =====================================
// 3) OBTENER HISTORIAL DE VALIDACIONES
// =====================================

$hist = $conn->query("
    SELECT v.fecha_validacion, v.metodo, com.nombre AS comercio
    FROM validaciones v
    LEFT JOIN comercios com ON v.comercio_id = com.id
    WHERE v.cupon_id = $cup_id
    ORDER BY v.fecha_validacion DESC
");


// Auxiliar
function fechaBonita($f) {
    if (!$f) return "—";
    return date("d/m/Y H:i", strtotime($f));
}

$estadoColor = [
    "activo" => "#27ae60",
    "usado" => "#7f8c8d",
    "caducado" => "#c0392b"
][$cup["estado"]];
?>

<style>
.cupon-box {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin: 20px 0;
}
.cupon-title {
    font-size: 26px;
    font-weight: bold;
}
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    color: white;
    font-weight: bold;
}
.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-top: 20px;
}
.casilla {
    padding: 15px;
    border-radius: 12px;
    background: #ecf0f1;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    border: 2px solid #bdc3c7;
}
.casilla.marcada {
    background: #27ae60;
    color: white;
    border-color: #1e8449;
}
.qr-box img {
    width: 280px;
    display: block;
    margin: 0 auto;
}
</style>

<h1>Ver Cupón</h1>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cup["titulo"]) ?></div>

    <p><?= nl2br(htmlspecialchars($cup["descripcion"])) ?></p>

    <p><strong>Estado:</strong>
        <span class="badge" style="background:<?= $estadoColor ?>">
            <?= strtoupper($cup["estado"]) ?>
        </span>
    </p>

    <p><strong>Caduca:</strong> <?= fechaBonita($cup["fecha_caducidad"]) ?></p>

    <hr>

    <h3>Usuario</h3>
    <p>
        <?= htmlspecialchars($cup["usuario_nombre"] ?: "—") ?><br>
        Tel: <?= htmlspecialchars($cup["usuario_telefono"] ?: "—") ?>
    </p>

    <h3>Comercio asignado</h3>
    <p><?= htmlspecialchars($cup["comercio_nombre"] ?: "—") ?></p>

    <hr>

    <?php if ($cup["qr_path"]): ?>
    <div class="qr-box">
        <h3>Código QR</h3>
        <img src="/<?= $cup["qr_path"] ?>" alt="QR del cupón">
    </div>
    <?php endif; ?>

    <hr>

    <h3>Casillas</h3>
    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
        <div class="casilla <?= $c["marcada"] ? 'marcada' : '' ?>">
            <?= $c["numero_casilla"] ?>
            <?php if ($c["marcada"]): ?>
                <div style="font-size:11px; margin-top:4px;">
                    <?= fechaBonita($c["fecha_marcada"]) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <h3>Historial de Validaciones</h3>

    <?php if ($hist->num_rows > 0): ?>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Método</th>
            <th>Comercio</th>
        </tr>

        <?php while ($h = $hist->fetch_assoc()): ?>
        <tr>
            <td><?= fechaBonita($h["fecha_validacion"]) ?></td>
            <td><?= strtoupper($h["metodo"]) ?></td>
            <td><?= htmlspecialchars($h["comercio"] ?: "—") ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php else: ?>
        <p>No hay validaciones registradas.</p>
    <?php endif; ?>

</div>

<?php include "_footer.php"; ?>
