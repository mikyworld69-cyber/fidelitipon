<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// =======================================
// OBTENER COMERCIOS + ESTADÍSTICAS
// =======================================
$sql = $conn->query("
    SELECT 
        c.id,
        c.nombre,
        c.logo,
        (
            SELECT COUNT(*) FROM cupones WHERE comercio_id = c.id
        ) AS total_cupones,
        (
            SELECT COUNT(*) FROM cupones WHERE comercio_id = c.id AND estado='activo'
        ) AS cupones_activos,
        (
            SELECT COUNT(*) FROM cupones WHERE comercio_id = c.id AND estado='usado'
        ) AS cupones_usados,
        (
            SELECT COUNT(*) FROM cupones WHERE comercio_id = c.id AND estado='caducado'
        ) AS cupones_caducados
    FROM comercios c
    ORDER BY c.nombre ASC
");
?>

<h1>Comercios</h1>

<style>
.comercio-card {
    background: white;
    padding: 18px;
    border-radius: 16px;
    margin-bottom: 15px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    align-items: center;
}

.logo-comercio {
    width: 90px;
    height: 90px;
    object-fit: contain;
    border-radius: 12px;
    background: #f7f7f7;
    padding: 8px;
    border: 1px solid #eee;
}

.comercio-info h3 {
    margin: 0 0 5px;
}

.comercio-info p {
    margin: 3px 0;
    font-size: 14px;
    color: #555;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    font-size: 11px;
    border-radius: 8px;
    margin: 2px;
    color: white;
    font-weight: bold;
}

.badge-total { background:#3498db; }
.badge-activo { background:#2ecc71; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#e74c3c; }

.actions a {
    display: block;
    margin-bottom: 6px;
    padding: 7px 12px;
    background: #2980b9;
    color:white;
    text-decoration:none;
    border-radius: 8px;
    font-size: 13px;
    text-align:center;
}
.actions a:hover { background:#1f6fa3; }
</style>

<?php while ($c = $sql->fetch_assoc()): ?>

<div class="comercio-card">

    <!-- Logo -->
    <img src="<?= $c["logo"] ?: '/img/default_logo.png' ?>" class="logo-comercio">

    <!-- Info -->
    <div class="comercio-info">
        <h3><?= htmlspecialchars($c["nombre"]) ?></h3>

        <span class="badge badge-total">Total: <?= $c["total_cupones"] ?></span>
        <span class="badge badge-activo">Activos: <?= $c["cupones_activos"] ?></span>
        <span class="badge badge-usado">Usados: <?= $c["cupones_usados"] ?></span>
        <span class="badge badge-caducado">Caducados: <?= $c["cupones_caducados"] ?></span>
    </div>

    <!-- Acciones -->
    <div class="actions">
        <a href="ver_comercio.php?id=<?= $c["id"] ?>">👁 Ver</a>
        <a href="editar_comercio.php?id=<?= $c["id"] ?>">✏️ Editar</a>
        <a href="eliminar_comercio.php?id=<?= $c["id"] ?>" 
           onclick="return confirm('¿Eliminar este comercio y TODOS sus cupones asociados?');">
           🗑 Eliminar
        </a>
    </div>

</div>

<?php endwhile; ?>

<?php include "_footer.php"; ?>
