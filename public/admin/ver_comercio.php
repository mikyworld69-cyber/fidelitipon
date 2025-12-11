<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Comercio no especificado.";
    exit;
}

$comercio_id = intval($_GET["id"]);

// ================================
// OBTENER DATOS DEL COMERCIO
// ================================
$sql = $conn->prepare("
    SELECT *
    FROM comercios
    WHERE id = ?
");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    echo "Comercio no encontrado.";
    exit;
}

// ================================
// OBTENER CUPONES RELACIONADOS
// ================================
$qCupones = $conn->prepare("
    SELECT id, codigo, titulo, estado, fecha_caducidad, total_casillas
    FROM cupones
    WHERE comercio_id = ?
    ORDER BY id DESC
");
$qCupones->bind_param("i", $comercio_id);
$qCupones->execute();
$cupones = $qCupones->get_result();

// Logo
$logo = $comercio["logo"] ?: "/img/default_logo.png";

?>

<style>
.comercio-box {
    width: 90%;
    background: white;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.logo-comercio {
    width: 140px;
    height: 140px;
    object-fit: contain;
    border-radius: 18px;
    display: block;
    margin: 0 auto 20px;
}

.info-line {
    font-size: 15px;
    margin: 5px 0;
    color: #555;
}

.cupon-card {
    background: white;
    padding: 15px;
    margin: 15px 0;
    border-radius: 16px;
    display: grid;
    grid-template-columns: 120px 1fr 120px;
    gap: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

/* Badges */
.badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    color:white;
    font-weight:bold;
}
.badge-activo { background:#2ecc71; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#e74c3c; }

/* Donut */
.donut {
    width: 110px;
    height: 110px;
    margin: auto;
}

.donut-text {
    fill: #333;
    font-size: 17px;
    font-weight: bold;
}

/* Actions */
.actions a {
    display: inline-block;
    margin-right: 5px;
    padding: 9px 14px;
    background:#2980b9;
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
}
.actions a:hover { background:#1f6fa3; }

/* Botones finales */
.btn-back {
    background:#7f8c8d;
    color:white;
    padding:12px 18px;
    border-radius:10px;
    text-decoration:none;
}
.btn-edit {
    background:#3498db;
    color:white;
    padding:12px 18px;
    border-radius:10px;
    text-decoration:none;
}
.btn-delete {
    background:#e74c3c;
    color:white;
    padding:12px 18px;
    border-radius:10px;
    text-decoration:none;
}
</style>


<div class="comercio-box">

    <img src="<?= $logo ?>" class="logo-comercio">

    <h2 style="text-align:center;"><?= htmlspecialchars($comercio["nombre"]) ?></h2>

    <p class="info-line">🆔 ID Comercio: <strong><?= $comercio_id ?></strong></p>

    <div style="margin-top:20px; text-align:center;">
        <a href="editar_comercio.php?id=<?= $comercio_id ?>" class="btn-edit">✏️ Editar Comercio</a>
        <a href="eliminar_comercio.php?id=<?= $comercio_id ?>"
           onclick="return confirm('¿Eliminar este comercio y TODOS sus cupones?');"
           class="btn-delete">🗑 Eliminar Comercio</a>
        <a href="comercios.php" class="btn-back">⬅ Volver</a>
    </div>

    <hr><br>

    <h3>Cupones Emitidos</h3>

    <?php if ($cupones->num_rows == 0): ?>
        <p>No hay cupones asociados.</p>
    <?php endif; ?>

    <?php while ($c = $cupones->fetch_assoc()): ?>

        <?php
        // Obtener casillas del cupón
        $qCas = $conn->prepare("
            SELECT COUNT(*) AS usadas
            FROM cupon_casillas
            WHERE cupon_id = ? AND estado = 1
        ");
        $qCas->bind_param("i", $c["id"]);
        $qCas->execute();
        $usadas = $qCas->get_result()->fetch_assoc()["usadas"];

        $total = $c["total_casillas"];
        $porcentaje = $total > 0 ? round(($usadas / $total) * 100) : 0;

        $badgeClass =
            ($c["estado"] === "usado" ? "badge-usado" :
            ($c["estado"] === "caducado" ? "badge-caducado" : "badge-activo"));
        ?>

        <div class="cupon-card">

            <!-- Donut -->
            <svg class="donut" viewBox="0 0 36 36">
                <path 
                    d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#eee"
                    stroke-width="3" />

                <path 
                    d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#3498db"
                    stroke-width="3"
                    stroke-dasharray="<?= $porcentaje ?>, 100" />

                <text x="18" y="20.35" class="donut-text" text-anchor="middle">
                    <?= $porcentaje ?>%
                </text>
            </svg>

            <!-- Info -->
            <div>
                <h3><?= htmlspecialchars($c["titulo"]) ?></h3>
                <p><strong>Código:</strong> <?= $c["codigo"] ?></p>
                <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></p>
                <span class="badge <?= $badgeClass ?>"><?= strtoupper($c["estado"]) ?></span>
            </div>

            <!-- Actions -->
            <div class="actions" style="text-align:right;">
                <a href="ver_cupon.php?id=<?= $c["id"] ?>">👁 Ver</a>
                <a href="editar_cupon.php?id=<?= $c["id"] ?>">✏️ Editar</a>
            </div>

        </div>

    <?php endwhile; ?>

</div>

<?php include "_footer.php"; ?>
