<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Usuario no especificado.";
    exit;
}

$user_id = intval($_GET["id"]);

// ================================
// OBTENER DATOS DEL USUARIO
// ================================
$sql = $conn->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
");
$sql->bind_param("i", $user_id);
$sql->execute();
$usuario = $sql->get_result()->fetch_assoc();

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

// ================================
// OBTENER CUPONES DEL USUARIO
// ================================
$cupones_q = $conn->prepare("
    SELECT id, codigo, titulo, estado, fecha_caducidad, total_casillas
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$cupones_q->bind_param("i", $user_id);
$cupones_q->execute();
$cupones = $cupones_q->get_result();
?>

<style>
.user-box {
    width: 90%;
    background: white;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

h2 {
    margin-top: 0;
}

.info-line {
    margin: 5px 0;
    font-size: 15px;
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

.badge {
    padding: 6px 12px;
    border-radius: 8px;
    color:white;
    font-size: 12px;
    font-weight: bold;
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

/* Acciones */
.actions a {
    display: inline-block;
    padding: 9px 14px;
    margin-right: 5px;
    background:#2980b9;
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
}
.actions a:hover { background:#1f6fa3; }
</style>

<div class="user-box">

    <h2>👤 <?= htmlspecialchars($usuario["nombre"]) ?></h2>

    <p class="info-line">📱 Teléfono: <strong><?= $usuario["telefono"] ?></strong></p>
    <p class="info-line">📅 Registrado: <strong><?= date("d/m/Y", strtotime($usuario["fecha_registro"])) ?></strong></p>

    <br>

    <div class="actions">
        <a href="editar_usuario.php?id=<?= $user_id ?>">✏️ Editar usuario</a>
        <a href="eliminar_usuario.php?id=<?= $user_id ?>" 
           onclick="return confirm('¿Eliminar este usuario y TODOS sus cupones?');">
           🗑 Eliminar usuario
        </a>
        <a href="usuarios.php">⬅️ Volver</a>
    </div>

    <hr><br>

    <h3>Cupones del Usuario</h3>

    <?php if ($cupones->num_rows === 0): ?>
        <p>No tiene cupones asignados.</p>
    <?php endif; ?>

    <?php while ($c = $cupones->fetch_assoc()): ?>

        <?php
            // Obtener casillas usadas
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

            <div>
                <h3><?= htmlspecialchars($c["titulo"]) ?></h3>
                <p><strong>Código:</strong> <?= $c["codigo"] ?></p>
                <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?></p>
                <span class="badge <?= $badgeClass ?>"><?= strtoupper($c["estado"]) ?></span>
            </div>

            <div class="actions" style="text-align:right;">
                <a href="ver_cupon.php?id=<?= $c["id"] ?>">👁 Ver Cupón</a>
                <a href="editar_cupon.php?id=<?= $c["id"] ?>">✏️ Editar Cupón</a>
            </div>

        </div>

    <?php endwhile; ?>

</div>

<?php include "_footer.php"; ?>
