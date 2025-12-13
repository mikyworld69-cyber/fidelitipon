<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("ID no recibido.");
}

$cup_id = intval($_GET["id"]);

// Obtener cupón
$sql = $conn->prepare("
    SELECT 
        c.*, 
        u.nombre AS usuario_nombre,
        u.telefono AS usuario_telefono,
        com.nombre AS comercio_nombre,
        com.logo AS comercio_logo
    FROM cupones c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado.");
}

// Obtener casillas
$cas = $conn->prepare("
    SELECT numero_casilla, marcada 
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$cas->bind_param("i", $cup_id);
$cas->execute();
$casillas = $cas->get_result()->fetch_all(MYSQLI_ASSOC);

include "_header.php";
?>

<h1>Detalles del Cupón</h1>

<div class="card">

<h2><?= htmlspecialchars($cup["titulo"]) ?></h2>

<p><?= nl2br(htmlspecialchars($cup["descripcion"])) ?></p>

<p><strong>Código:</strong> <?= $cup["codigo"] ?></p>
<p><strong>Estado:</strong> <?= $cup["estado"] ?></p>
<p><strong>Caducidad:</strong> <?= $cup["fecha_caducidad"] ?: "Sin fecha" ?></p>

<?php if ($cup["qr_path"]): ?>
    <h3>QR del Cupón</h3>
    <img src="/<?= $cup["qr_path"] ?>" style="width:200px; border:1px solid #ccc; padding:10px; border-radius:10px;">
<?php endif; ?>

<h3>Usuario</h3>
<p><?= $cup["usuario_nombre"] ?> (<?= $cup["usuario_telefono"] ?>)</p>

<h3>Comercio</h3>
<p><?= $cup["comercio_nombre"] ?></p>

<?php if ($cup["comercio_logo"]): ?>
    <img src="/uploads/comercios/<?= $cup["comercio_logo"] ?>" style="width:120px; margin-top:10px;">
<?php endif; ?>

<h3>Casillas</h3>

<div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:10px;">
<?php foreach ($casillas as $c): ?>
    <div style="
        padding:15px; 
        text-align:center; 
        border-radius:8px;
        background: <?= $c["marcada"] ? '#1abc9c' : '#f4f4f4' ?>;
        color: <?= $c["marcada"] ? 'white' : 'black' ?>;
        border:1px solid #ccc;
    ">
        <?= $c["numero_casilla"] ?>
    </div>
<?php endforeach; ?>
</div>

<br>

<a href="generar_pdf.php?id=<?= $cup_id ?>" 
   class="btn" 
   style="background:#8e44ad; color:white; padding:12px; display:inline-block; margin-top:20px; border-radius:10px;">
    📄 Descargar PDF
</a>

</div>

<?php include "_footer.php"; ?>
