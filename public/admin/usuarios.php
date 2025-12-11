<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// =======================================
// OBTENER USUARIOS Y SUS ESTADÍSTICAS
// =======================================
$sql = $conn->query("
    SELECT 
        u.id,
        u.nombre,
        u.telefono,
        u.fecha_registro,
        (
            SELECT COUNT(*) FROM cupones WHERE usuario_id = u.id
        ) AS total_cupones,
        (
            SELECT COUNT(*) FROM cupones WHERE usuario_id = u.id AND estado='activo'
        ) AS cupones_activos,
        (
            SELECT COUNT(*) FROM cupones WHERE usuario_id = u.id AND estado='usado'
        ) AS cupones_usados,
        (
            SELECT COUNT(*) FROM cupones WHERE usuario_id = u.id AND estado='caducado'
        ) AS cupones_caducados
    FROM usuarios u
    ORDER BY u.nombre ASC
");
?>

<h1>Usuarios</h1>

<style>
.user-card {
    background: white;
    padding: 18px;
    border-radius: 16px;
    margin-bottom: 15px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 20px;
}

.user-info h3 {
    margin: 0 0 5px;
}

.user-info p {
    margin: 3px 0;
    font-size: 14px;
    color: #555;
}

.stats {
    text-align: right;
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

.badge-activo { background:#2ecc71; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#e74c3c; }
.badge-total { background:#3498db; }

.actions a {
    display: block;
    margin-bottom: 6px;
    padding: 7px 12px;
    background: #2980b9;
    color:white;
    text-decoration:none;
    border-radius: 8px;
    font-size: 13px;
}
.actions a:hover { background:#1f6fa3; }
</style>

<?php while ($u = $sql->fetch_assoc()): ?>

<div class="user-card">

    <!-- INFO -->
    <div class="user-info">
        <h3><?= htmlspecialchars($u["nombre"]) ?></h3>
        <p>📱 <?= $u["telefono"] ?></p>
        <p>🗓 Registrado: <?= date("d/m/Y", strtotime($u["fecha_registro"])) ?></p>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="stats">
        <span class="badge badge-total">Total: <?= $u["total_cupones"] ?></span><br>
        <span class="badge badge-activo">Activos: <?= $u["cupones_activos"] ?></span><br>
        <span class="badge badge-usado">Usados: <?= $u["cupones_usados"] ?></span><br>
        <span class="badge badge-caducado">Caducados: <?= $u["cupones_caducados"] ?></span>
    </div>

    <!-- ACCIONES -->
    <div class="actions">
        <a href="ver_usuario.php?id=<?= $u["id"] ?>">👁 Ver</a>
        <a href="editar_usuario.php?id=<?= $u["id"] ?>">✏️ Editar</a>
        <a href="eliminar_usuario.php?id=<?= $u["id"] ?>"
           onclick="return confirm('¿Eliminar este usuario y todos sus cupones?');">
           🗑 Eliminar
        </a>
    </div>

</div>

<?php endwhile; ?>

<?php include "_footer.php"; ?>
