<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si ya está logueado
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Consulta por email

    $sql = $conn->prepare("
        SELECT id, usuario, email, password 
        FROM admin 
        WHERE email = ?
        LIMIT 1
    ");
    $sql->bind_param("s", $email);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {

        $admin = $res->fetch_assoc(); // SOLO AQUÍ SE LEE

        // Verificar contraseña
        var_dump("PASSWORD ENVIADA:", $password);
var_dump("HASH BD:", $admin["password"]);
var_dump("VERIFY:", password_verify($password, $admin["password"]));
exit;


    } else {
        $mensaje = "❌ El correo no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Login | Fidelitipon</title>
<link rel="stylesheet" href="admin.css">
</head>

<body>
<div class="box">
    <h2>Acceso Admin</h2>

    <?php if ($mensaje): ?>
    <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" class="input" placeholder="Correo electrónico" required>
        <input type="password" name="password" class="input" placeholder="Contraseña" required>
        <button class="btn">Entrar</button>
    </form>
</div>
</body>
</html>
