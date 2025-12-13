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

$id = intval($_GET["id"]);

$sql = $conn->prepare("
    SELECT c.*, com.nombre AS comercio, u.nombre AS usuario
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?
");
$sql->bind_param("i", $id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) die("Cupón no encontrado.");

include "_header.php";
?>

<h1>Cupón ID #<?= $cup["id"] ?></h1>

<div class="card">

    <p><strong>Título:</strong> <?= htmlspecialchars($cup["titulo"]) ?></p>
    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($cup["descripcion"])) ?></p>

    <p><strong>Estado:</strong> <?= strtoupper($cup["estado"]) ?></p>

    <p><strong>Comercio:</strong> <?= $cup["comercio"] ?: "—" ?></p>
    <p><strong>Usuario:</strong> <?= $cup["usuario"] ?: "—" ?></p>

    <p><strong>Caducidad:</strong>
        <?= $cup["fecha_caducidad"]
            ? date("d/m/Y", strtotime($cup["fecha_caducidad"]))
            : "—" ?>
    </p>

    <br>

    <?php if (!empty($cup["qr_path"])): ?>
        <img src="/file.php?type=qr&file=<?= basename($cup["qr_path"]) ?>"
             width="200" style="border:1px solid #ddd;border-radius:8px;padding:10px;">
    <?php else: ?>
        <p style="color:#c0392b;">⚠ Este cupón no tiene QR generado.</p>
    <?php endif; ?>

</div>

<?php include "_footer.php"; ?>
