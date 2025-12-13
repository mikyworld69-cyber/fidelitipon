<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$id = intval($_GET["id"]);

$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$com = $sql->get_result()->fetch_assoc();

if (!$com) {
    die("Comercio no encontrado.");
}

include "_header.php";
?>

<h1>Información del Comercio</h1>

<div class="card">

    <?php if (!empty($com["logo"])): ?>
        <img src="/file.php?type=comercio&file=<?= basename($com["logo"]) ?>"
             width="140" style="border-radius:10px; margin-bottom:15px;">
    <?php endif; ?>

    <p><strong>Nombre:</strong> <?= htmlspecialchars($com["nombre"]) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($com["telefono"] ?: "—") ?></p>

    <a class="btn-success" href="editar_comercio.php?id=<?= $com["id"] ?>">✏ Editar Comercio</a>
</div>

<?php include "_footer.php"; ?>
