<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include __DIR__ . '/../includes/head_app.php';

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

// 1. Verificar que pertenece al usuario
$sql = $conn->prepare("
    SELECT id, titulo, estado 
    FROM cupones 
    WHERE id = ? AND usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado o no te pertenece.");
}

if ($cup["estado"] !== "activo") {
    die("Este cupón ya no está disponible para usarse.");
}

// 2. Actualizar estado a usado
$update = $conn->prepare("
    UPDATE cupones 
    SET estado = 'usado', fecha_uso = NOW()
    WHERE id = ?
");
$update->bind_param("i", $cup_id);
$update->execute();

?>
<style>
.success-box {
    background: white;
    margin: 30px;
    padding: 25px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.success-icon {
    font-size: 70px;
    color: #27ae60;
    margin-bottom: 15px;
}

.success-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.success-text {
    font-size: 16px;
    color: #444;
    margin-bottom: 20px;
}

.btn-back {
    display: block;
    width: 100%;
    padding: 14px;
    background: #3498db;
    color: white;
    border-radius: 12px;
    font-size: 18px;
    text-decoration: none;
}

.btn-back:hover {
    background: #2980b9;
}
</style>

<div class="app-header">Cupón Usado</div>

<div class="success-box">

    <div class="success-icon">✅</div>

    <div class="success-title">Cupón Canjeado</div>

    <div class="success-text">
        Has usado el cupón:<br>
        <strong><?= htmlspecialchars($cup["titulo"]) ?></strong><br>
        ¡Gracias por utilizar Fidelitipon!
    </div>

    <a href="panel_usuario.php" class="btn-back">Volver a mis cupones</a>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>
