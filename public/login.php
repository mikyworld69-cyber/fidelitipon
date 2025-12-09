<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $telefono  = trim($_POST['telefono']);
    $password  = trim($_POST['password']);

    $sql = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
    $sql->bind_param("s", $telefono);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            header("Location: app/panel_usuario.php");
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "No existe un usuario con ese teléfono.";
    }
}
?>
<link rel="stylesheet" href="/public/app/app.css">

<!DOCTYPE html>
<html>
<head>
<title>Fidelitipon - Acceso</title>
<link rel="stylesheet" href="assets/css/app.css">
<link rel="manifest" href="/public/manifest.json">
<meta name="theme-color" content="#3498db">
<link rel="manifest" href="/public/manifest.json">
<meta name="theme-color" content="#3498db">

</head>
<body>

<div class="login-box">
    <h2>Acceso a Fidelitipon</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button>Entrar</button>
    </form>

    <a href="register.php">Crear cuenta</a>
</div>
<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/public/sw-pwa.js")
        .then(() => console.log("SW PWA registrado"))
        .catch(err => console.error("Error SW PWA", err));
}
</script>

</body>
</html>
