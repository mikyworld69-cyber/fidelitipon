<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener cupones
$sql = $conn->query("
    SELECT c.id, c.codigo, c.titulo, c.descripcion, c.estado, 
           c.fecha_caducidad, c.total_casillas,
           u.nombre AS usuario_nombre,
           com.nombre AS comercio_nombre
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    ORDER BY c.id DESC
");

include "_header.php";
?>

<h1>Cupones</h1>

<style>
.cupon-card {
    background: white;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 5px 16px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 120px 1fr 120px;
    gap: 20px;
    align-items: center;
}

.cupon-info h3 {
    margin: 0 0 5px;
}
.cupon-info p {
    margin: 3px 0;
    color: #666;
    font-size: 14px;
}

/* BADGES */
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
    color: white;
}
.badge-activo { background: #2ecc71; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #e74c3c; }

/* DONUT SVG */
.donut {
    width: 110px;
    height: 110px;
    display: block;
    margin: auto;
}
.donut-text {
    font-size: 18px;
    font-weight: bold;
    fill: #333;
}

/* ACCIONES */
.actions a {
    display: inline-block;
    background: #2980b9;
    padding: 7px 14px;
    color:white;
    font-size: 12px;
    border-radius: 8px;
    margin-right: 6px;
    text-decoration: none;
}
.actions a:hover {
    background:#1f6fa3;
}
</style>

<?php while ($c = $sql->fetch_assoc()): ?>

<?php
    // obtener cuántas casillas están marcadas
    $q = $conn->prepare("
        SELECT COUNT(*) AS usadas
        FROM cupon_casillas
        WHERE cupon_id = ? AND estado = 1
    ");
    $q->bind_param("i", $c["id"]);
    $q->execute();
    $usadas = $q->get_result()->fetch_assoc()["usadas"];

    $total = $c["total_casillas"];
    $porcentaje = round(($usadas / $total) * 100);

    // estado del cupón
    $estadoClass =
        ($c["estado"] === "usado" ? "badge-usado" :
        ($c["estado"] === "caducado" ? "badge-caducado" : "badge-activo"));
?>

<div class="cupon-card">

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

    <!-- INFO DEL CUPÓN -->
    <div class="cupon-info">
        <h3><?= htmlspecialchars($c["titulo"]) ?></h3>

        <p><strong>Código:</strong> <?= $c["codigo"] ?></p>

        <p><strong>Usuario:</strong> 
            <?= $c["usuario_nombre"] ? htmlspecialchars($c["usuario_nombre"]) : "—" ?>
        </p>

        <p><strong>Comercio:</strong> <?= htmlspecialchars($c["comercio_nombre"]) ?></p>

        <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></p>

        <span class="badge <?= $estadoClass ?>"><?= strtoupper($c["estado"]) ?></span>

        <p style="margin-top:6px;">
            <?= $usadas ?>/<?= $total ?> casillas usadas
        </p>
    </div>

    <!-- ACCIONES -->
    <div class="actions">
        <a href="ver_cupon.php?id=<?= $c["id"] ?>">Ver</a>
        <a href="editar_cupon.php?id=<?= $c["id"] ?>">Editar</a>
        <a href="eliminar_cupon.php?id=<?= $c["id"] ?>" 
           onclick="return confirm('¿Eliminar este cupón?');"
        >Eliminar</a>
    </div>

</div>

<?php endwhile; ?>

<?php include "_footer.php"; ?>
