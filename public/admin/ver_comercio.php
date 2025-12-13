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

$comercio_id = intval($_GET["id"]);

$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

include "_header.php";
?>

<h1>Comercio: <?= htmlspecialchars($comercio["nombre"]) ?></h1>

<div class="card">

<p><strong>Logo:</strong></p>

<?php if ($comercio["logo"]): ?>
    <img src="/<?= $comercio["logo"] ?>" style="max-width:180px;border-radius:10px;">
<?php else: ?>
    <em>No hay logo</em>
<?php endif; ?>

<br><br>

<a href="editar_comercio.php?id=<?= $comercio_id ?>" class="btn-primary">Editar Comercio</a>
<a href="subir_logo.php?id=<?= $comercio_id ?>" class="btn-secondary">Subir Logo</a>

</div>

<?php include "_footer.php"; ?>
