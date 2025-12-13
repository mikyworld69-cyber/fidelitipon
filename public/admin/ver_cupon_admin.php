<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: cupones.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// Obtener datos del cupón
$sql = $conn->prepare("
    SELECT 
        c.id,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_caducidad,
        c.codigo,
        c.qr_path,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado.");
}

// Obtener casillas
$sqlCas = $conn->prepare("
    SELECT numero_casilla, marcada, fecha_marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sqlCas->bind_param("i", $cup_id);
$sqlCas->execute();
$casillas = $sqlCas->get_result();

// Obtener validaciones
$sqlVal = $conn->prepare("
    SELECT casilla, fecha_validacion, metodo
    FROM validaciones
    WHERE cupon_id = ?
    ORDER BY fecha_validacion ASC
");
$sqlVal->bind_param("i", $cup_id);
$sqlVal->execute();
$validaciones = $sqlVal->get_result();

include "_header.php";
?>

<style>
.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.10);
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #3498db;
    padding: 10px;
    color: white;
}
td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
.badge {
    padding: 6px 10px;
    border-radius: 8px;
    color: white;
}
.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }
</style>

<h1>Detalles del Cupón</h1>

<div class="card">
    <p><strong>Título:</strong> <?= htmlspecialchars($cup["titulo"]) ?></p>
    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($cup["descripcion"])) ?></p>

    <p><strong>Estado:</strong>
        <span class="badge 
            <?= $cup["estado"] === "activo" ? "badge-activo" : ($cup["estado"] === "usado" ? "badge-usado" : "badge-caducado") ?>">
            <?= strtoupper($cup["estado"]) ?>
        </span>
    </p>

    <p><strong>Fecha de caducidad:</strong>
        <?php if (!empty($cup["fecha_caducidad"])): ?>
            <?= date("d/m/Y", strtotime($cup["fecha_caducidad"])) ?>
        <?php else: ?>
            <span style="color:#7f8c8d;">Sin caducidad</span>
        <?php endif; ?>
    </p>

    <p><strong>Usuario:</strong> <?= htmlspecialchars($cup["usuario_nombre"] ?: "—") ?></p>
    <p><strong>Comercio:</strong> <?= htmlspecialchars($cup["comercio_nombre"] ?: "—") ?></p>

</div>

<?php if ($cup["qr_path"]): ?>
<div class="card" style="text-align:center;">
    <h3>Código QR</h3>
    <img src="/<?= $cup["qr_path"] ?>" width="230" alt="QR">
    <p><strong>Código:</strong> <?= htmlspecialchars($cup["codigo"]) ?></p>
</div>
<?php endif; ?>

<div class="card">
    <h3>Casillas del Cupón</h3>
    <table>
        <tr>
            <th>Casilla</th>
            <th>Marcada</th>
            <th>Fecha</th>
        </tr>

        <?php while ($c = $casillas->fetch_assoc()): ?>
        <tr>
            <td><?= $c["numero_casilla"] ?></td>
            <td><?= $c["marcada"] ? "✔ Sí" : "❌ No" ?></td>
            <td><?= $c["fecha_marcada"] ?: "—" ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="card">
    <h3>Validaciones Realizadas</h3>

    <?php if ($validaciones->num_rows === 0): ?>
        <p>No hay validaciones todavía.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Casilla</th>
            <th>Fecha</th>
            <th>Método</th>
        </tr>

        <?php while ($v = $validaciones->fetch_assoc()): ?>
        <tr>
            <td><?= $v["casilla"] ?></td>
            <td><?= date("d/m/Y H:i", strtotime($v["fecha_validacion"])) ?></td>
            <td><?= strtoupper($v["metodo"]) ?></td>
        </tr>
        <?php endwhile; ?>

    </table>
    <?php endif; ?>

</div>

<?php include "_footer.php"; ?>
