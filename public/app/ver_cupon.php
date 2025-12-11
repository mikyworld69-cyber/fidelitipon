<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

/* ============================
   CONTROL DE ACCESO
============================ */

// Caso 1 → Usuario normal autenticado
if (isset($_SESSION["usuario_id"])) {
    $user_id = intval($_SESSION["usuario_id"]);
    $acceso_admin = false;

// Caso 2 → Admin autenticado viendo cupón de usuario
} elseif (isset($_SESSION["admin_id"])) {
    $user_id = null; // Admin puede ver cualquier cupón
    $acceso_admin = true;

// Caso 3 → Nadie autenticado → fuera
} else {
    header("Location: login.php");
    exit;
}

/* ============================
   VALIDAR ID CUPÓN
============================ */

if (!isset($_GET["id"])) {
    if ($acceso_admin) {
        header("Location: /admin/usuarios.php");
    } else {
        header("Location: panel_usuario.php");
    }
    exit;
}

$cup_id = intval($_GET["id"]);

/* ============================
   OBTENER DATOS DEL CUPÓN
============================ */

if ($acceso_admin) {
    // ADMIN → puede ver cualquier cupón
    $sql = $conn->prepare("
        SELECT id, usuario_id, titulo, descripcion, estado, fecha_caducidad, total_casillas, casillas_marcadas
        FROM cupones
        WHERE id = ?
    ");
    $sql->bind_param("i", $cup_id);

} else {
    // USUARIO → solo sus cupones
    $sql = $conn->prepare("
        SELECT id, usuario_id, titulo, descripcion, estado, fecha_caducidad, total_casillas, casillas_marcadas
        FROM cupones
        WHERE id = ? AND usuario_id = ?
    ");
    $sql->bind_param("ii", $cup_id, $user_id);
}

$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("⚠ El cupón no existe o no tienes permiso para verlo.");
}

$estado = strtoupper($cupon["estado"]);

/* ============================
   OBTENER CASILLAS
============================ */

$sqlCas = $conn->prepare("
    SELECT id, numero_casilla, marcada, fecha_marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sqlCas->bind_param("i", $cup_id);
$sqlCas->execute();
$casillas = $sqlCas->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Cupón</title>

<link rel="stylesheet" href="/app/app.css">

<style>
body {
    background: #f5f6fa;
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding-bottom: 80px;
}

.cupon-box {
    background: white;
    margin: 20px;
    padding: 22px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
}

.cupon-title {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
}

.cupon-desc {
    font-size: 15px;
    color: #555;
    margin-bottom: 15px;
}

.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    color: white;
}

.badge-activo { background:#27ae60; }
.badge-usado { background:#7f8c8d; }
.badge-caducado { background:#c0392b; }

.casilla-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-top: 20px;
}

.casilla {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: #ecf0f1;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 18px;
    font-weight: bold;
}

.casilla.marcada {
    background: #3498db;
    color: white;
}
</style>
</head>

<body>

<div class="app-header">Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></div>

    <p><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?></p>

    <?php
        $badgeClass = "badge-activo";
        if ($estado === "USADO") $badgeClass = "badge-usado";
        if ($estado === "CADUCADO") $badgeClass = "badge-caducado";
    ?>

    <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>

    <?php if ($cupon["total_casillas"] > 0): ?>
        <h3 style="margin-top:25px;">Progreso</h3>

        <div class="casilla-grid">
            <?php while ($c = $casillas->fetch_assoc()): ?>
                <div class="casilla <?= ($c["marcada"] == 1) ? 'marcada' : '' ?>">
                    <?= $c["numero_casilla"] ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>

<div class="bottom-nav">
    <a href="/app/panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
