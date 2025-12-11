<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// ===========================
// VALIDAR ID
// ===========================
if (!isset($_GET["id"])) {
    die("Cupón no especificado.");
}

$cup_id = intval($_GET["id"]);

// ===========================
// OBTENER CUPÓN
// ===========================
$sql = $conn->prepare("
    SELECT c.*, 
           u.nombre AS usuario_nombre, 
           u.telefono AS usuario_telefono,
           com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado.");
}

// ===========================
// OBTENER CASILLAS
// ===========================
$cas = $conn->prepare("
    SELECT numero_casilla, marcada, estado
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$cas->bind_param("i", $cup_id);
$cas->execute();
$casillas = $cas->get_result();

include "_header.php";
?>

<h1>Ver Cupón</h1>

<div class="card">

    <h2><?= htmlspecialchars($cupon["titulo"]) ?></h2>
    <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></p>

    <p><strong>Código:</strong> <?= $cupon["codigo"] ?></p>
    <p><strong>Estado:</strong> <?= strtoupper($cupon["estado"]) ?></p>
    <p><strong>Caduca:</strong> <?= $cupon["fecha_caducidad"] ? date("d/m/Y H:i", strtotime($cupon["fecha_caducidad"])) : "—" ?></p>

    <h3>Usuario:</h3>
    <p><?= $cupon["usuario_nombre"] ? $cupon["usuario_nombre"] . " (" . $cupon["usuario_telefono"] . ")" : "No asignado" ?></p>

    <h3>Comercio:</h3>
    <p><?= $cupon["comercio_nombre"] ?></p>

    <h3>Casillas:</h3>
    <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:10px;">

    <?php while ($c = $casillas->fetch_assoc()): ?>
        <div style="
            padding:12px;
            text-align:center;
            border-radius:10px;
            background: <?= $c['marcada'] ? '#2ecc71' : '#bdc3c7' ?>;
            color:white;
        ">
            <?= $c["numero_casilla"] ?>
        </div>
    <?php endwhile; ?>

    </div>

</div>

<?php include "_footer.php"; ?>
