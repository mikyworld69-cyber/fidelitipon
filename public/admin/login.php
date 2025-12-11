<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si ya está logueado → entra
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// PROCESO LOGIN
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

        $admin = $res->fetch_assoc();

        // Verificación correcta de contraseña
        if (password_verify($password, $admin["password"])) {

            $_SESSION["admin_id"] = $admin["id"];
            header("Location: dashboard.php");
            exit;

        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }

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
<style>
body {
    background: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial;
}

.box {
    width: 350px;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

h2 {
    text-align: center;
    margin: 0 0 20px 0;
    color: #3498db;
}

.input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #bbb;
}

.btn {
    width: 100%;
    padding: 12px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    border: none;
    margin-top: 10px;
}

.btn:hover { 
    background: #2980b9; 
}

.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 15px;
}
</style>
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
