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
    SELECT c.*, u.nombre AS usuario_nombre, com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) die("Cupón no encontrado");

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

<h1>Detalles del Cupón</h1>

<div class="card">
    <p><strong>Título:</strong> <?= htmlspecialchars($cup["titulo"]) ?></p>
    <p><strong>Usuario:</strong> <?= htmlspecialchars($cup["usuario_nombre"] ?: "—") ?></p>
    <p><strong>Comercio:</strong> <?= htmlspecialchars($cup["comercio_nombre"] ?: "—") ?></p>
    <p><strong>Estado:</strong> <?= strtoupper($cup["estado"]) ?></p>
    <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cup["fecha_caducidad"])) ?></p>
</div>

<?php if ($cup["qr_path"]): ?>
<div class="card" style="text-align:center;">
    <h3>Código QR</h3>
    <img src="/<?= $cup["qr_path"] ?>" width="220">
    <p>Código: <strong><?= $cup["codigo"] ?></strong></p>
</div>
<?php endif; ?>

<div class="card">
    <h3>Casillas</h3>
    <table>
        <tr><th>#</th><th>Marcada</th><th>Fecha</th></tr>

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
        <p>No hay validaciones aún.</p>
    <?php else: ?>
        <table>
            <tr><th>Casilla</th><th>Fecha</th><th>Método</th></tr>

            <?php while ($v = $validaciones->fetch_assoc()): ?>
            <tr>
                <td><?= $v["casilla"] ?></td>
                <td><?= $v["fecha_validacion"] ?></td>
                <td><?= $v["metodo"] ?></td>
            </tr>
            <?php endwhile; ?>

        </table>
    <?php endif; ?>
</div>

<?php include "_footer.php"; ?>
