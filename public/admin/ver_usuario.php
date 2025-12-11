<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si no hay sesión admin → fuera
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar ID usuario recibido
if (!isset($_GET["id"])) {
    header("Location: usuarios.php");
    exit;
}

$usuario_id = intval($_GET["id"]);

// Obtener datos del usuario
$sqlUser = $conn->prepare("
    SELECT id, nombre, telefono, email, fecha_registro
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");
$sqlUser->bind_param("i", $usuario_id);
$sqlUser->execute();
$usuario = $sqlUser->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Obtener cupones del usuario
$sqlCupones = $conn->prepare("
    SELECT id, codigo, titulo, descripcion, estado, fecha_caducidad, total_casillas, casillas_marcadas, premium
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$sqlCupones->bind_param("i", $usuario_id);
$sqlCupones->execute();
$cupones = $sqlCupones->get_result();

include "_header.php";
?>

<style>
.user-box {
    background: white;
    padding: 20px;
    border-radius: 14px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.cupon-item {
    background: #fff;
    padding: 18px;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
}

.badge {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
    color: #fff;
    margin-left: 10px;
}

.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }
.badge-premium { background: #f39c12; }

.progress-bar {
    background: #e0e0e0;
    height: 8px;
    border-radius: 6px;
    margin-top: 10px;
}

.progress-fill {
    height: 8px;
    background: #3498db;
    border-radius: 6px;
}

.btn-ver {
    display: inline-block;
    background: #3498db;
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 12px;
}
</style>

<h1>Usuario: <?= htmlspecialchars($usuario["nombre"]) ?></h1>

<div class="user-box">
    <p><strong>📱 Teléfono:</strong> <?= htmlspecialchars($usuario["telefono"]) ?></p>
    <p><strong>📧 Email:</strong> <?= htmlspecialchars($usuario["email"]) ?></p>
    <p><strong>🕒 Registro:</strong> <?= $usuario["fecha_registro"] ?></p>
</div>

<h2>Cupones del Usuario</h2>

<?php if ($cupones->num_rows === 0): ?>
    <div class="user-box">
        <p>No tiene cupones asignados.</p>
    </div>
<?php endif; ?>

<?php while ($c = $cupones->fetch_assoc()): ?>

    <?php
        $estado = strtolower($c["estado"]);
        $badgeClass = "badge-activo";
        if ($estado === "usado") $badgeClass = "badge-usado";
        if ($estado === "caducado") $badgeClass = "badge-caducado";

        // progreso casillas
        $total = intval($c["total_casillas"]);
        $marcadas = intval($c["casillas_marcadas"]);
        $pct = ($total > 0) ? round(($marcadas / $total) * 100) : 0;
    ?>

    <div class="cupon-item">

        <h3>
            <?= htmlspecialchars($c["titulo"]) ?>
            <span class="badge <?= $badgeClass ?>"><?= strtoupper($c["estado"]) ?></span>

            <?php if ($c["premium"] == 1): ?>
                <span class="badge badge-premium">PREMIUM</span>
            <?php endif; ?>
        </h3>

        <p><?= nl2br(htmlspecialchars($c["descripcion"])) ?></p>

        <p><strong>Código:</strong> <?= $c["codigo"] ?></p>

        <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></p>

        <?php if ($total > 0): ?>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
            </div>
            <p><?= $marcadas ?>/<?= $total ?> casillas marcadas</p>
        <?php endif; ?>

        <a href="/app/ver_cupon.php?id=<?= $c['id'] ?>" class="btn-ver">Ver Cupón</a>

    </div>

<?php endwhile; ?>

<?php include "_footer.php"; ?>
