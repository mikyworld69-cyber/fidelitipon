<?php
require_once __DIR__ . '/../../config/db.php';

$token = $_GET["token"] ?? "";
$mensaje = "";

// Verificar token
$sql = $conn->prepare("SELECT id, reset_expira FROM admin WHERE reset_token=?");
$sql->bind_param("s", $token);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    die("Token inválido.");
}

$admin = $res->fetch_assoc();

// Verificar expiración
if (strtotime($admin["reset_expira"]) < time()) {
    die("El enlace ha expirado.");
}

// Actualizar contraseña
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pass1 = $_POST["password"];
    $pass2 = $_POST["password2"];

    if ($pass1 !== $pass2) {
        $mensaje = "❌ Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($pass1, PASSWORD_BCRYPT);

        $upd = $conn->prepare("UPDATE admin SET password=?, reset_token=NULL, reset_expira=NULL WHERE id=?");
        $upd->bind_param("si", $hash, $admin["id"]);
        $upd->execute();

        header("Location: login.php?reset=ok");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Restablecer contraseña</title>
<link rel="stylesheet" href="admin.css">
</head>

<body>
<div class="box">
    <h2>Nueva contraseña</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" class="input" name="password" placeholder="Nueva contraseña" required>
        <input type="password" class="input" name="password2" placeholder="Repetir contraseña" required>
        <button class="btn">Guardar contraseña</button>
    </form>
</div>
</body>
</html>
