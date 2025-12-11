<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Validar sesión usuario
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

if (!isset($_GET["id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$cup_id = intval($_GET["id"]);

// Verificar que el cupón pertenece al usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad, total_casillas, casillas_marcadas
    FROM cupones 
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cupon = $sql->get_result()->fetch_assoc();

if (!$cupon) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

$estado = strtoupper($cupon["estado"]);

$badgeClass = "badge-activo";
if ($estado === "USADO") $badgeClass = "badge-usado";
if ($estado === "CADUCADO") $badgeClass = "badge-caducado";

// Obtener casillas (compatible con Render)
$sqlCasillas = $conn->prepare("
    SELECT id, numero_casilla, marcada, fecha_marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$sqlCasillas->bind_param("i", $cup_id);
$sqlCasillas->execute();
$casillas = $sqlCasillas->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Cupón</title>

<link rel="stylesheet" href="/app/app.css">

<style>
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
    color: #fff;
}
.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }

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

<div class="app-header">Mi Cupón</div>

<div class="cupon-box">

    <div class="cupon-title"><?= htmlspecialchars($cupon["titulo"]) ?></div>

    <div class="cupon-desc"><?= nl2br(htmlspecialchars($cupon["descripcion"])) ?></div>

    <div><strong>Caduca:</strong> <?= date("d/m/Y", strtotime($cupon["fecha_caducidad"])) ?></div>

    <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>

    <h3 style="margin-top:25px;">Progreso</h3>

    <div class="casilla-grid">
    <?php while ($c = $casillas->fetch_assoc()): ?>
        <div class="casilla <?= ($c["marcada"] == 1) ? "marcada" : "" ?>">
            <?= $c["numero_casilla"] ?>
        </div>
    <?php endwhile; ?>
    </div>

</div>

<div class="bottom-nav">
    <a href="/app/panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
