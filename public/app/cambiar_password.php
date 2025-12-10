<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include __DIR__ . '/../includes/head_app.php'; // HEAD UNIVERSAL

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

$mensaje = "";
$tipo_mensaje = "ok";

// Obtener contraseña actual
$sql = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
$sql->bind_param("i", $user_id);
$sql->execute();
$datos = $sql->get_result()->fetch_assoc();
$pass_actual_hash = $datos["password"];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pass_actual = trim($_POST["pass_actual"]);
    $pass_nueva = trim($_POST["pass_nueva"]);
    $pass_confirm = trim($_POST["pass_confirm"]);

    if ($pass_actual === "" || $pass_nueva === "" || $pass_confirm === "") {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "error";
    } 
    elseif (!password_verify($pass_actual, $pass_actual_hash)) {
        $mensaje = "La contraseña actual no es correcta.";
        $tipo_mensaje = "error";
    }
    elseif ($pass_nueva !== $pass_confirm) {
        $mensaje = "Las contraseñas nuevas no coinciden.";
        $tipo_mensaje = "error";
    }
    elseif (strlen($pass_nueva) < 6) {
        $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    }
    else {
        // Actualizar contraseña
        $newHash = password_hash($pass_nueva, PASSWORD_DEFAULT);

        $up = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $up->bind_param("si", $newHash, $user_id);
        $up->execute();

        $mensaje = "Contraseña cambiada correctamente.";
        $tipo_mensaje = "ok";
    }
}
?>

<link rel="stylesheet" href="/app/app.css">

<style>
.container-box {
    background: white;
    padding: 20px;
    border-radius: 14px;
    margin: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.label {
    font-weight: bold;
    margin-bottom: 4px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}

.btn-save {
    width: 100%;
    padding: 14px;
    background: #3498db;
    color: white;
    border-radius: 12px;
    font-size: 17px;
    text-align: center;
    display: block;
    border: none;
}

.btn-save:hover {
    background: #2980b9;
}

.msg-ok {
    background: #1abc9c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.msg-error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 10px;
}
</style>

<div class="app-header">Cambiar Contraseña</div>

<div class="container-box">

    <?php if ($mensaje): ?>
        <div class="<?= $tipo_mensaje === 'ok' ? 'msg-ok' : 'msg-error' ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label class="label">Contraseña actual</label>
        <input type="password" name="pass_actual" placeholder="Tu contraseña actual">

        <label class="label">Nueva contraseña</label>
        <input type="password" name="pass_nueva" placeholder="Nueva contraseña">

        <label class="label">Confirmar nueva contraseña</label>
        <input type="password" name="pass_confirm" placeholder="Repite la nueva contraseña">

        <button class="btn-save">Guardar nueva contraseña</button>
    </form>

</div>

<div class="bottom-nav">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="perfil.php" class="active">👤 Perfil</a>
    <a href="../logout.php">🚪 Salir</a>
</div>
