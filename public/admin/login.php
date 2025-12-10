<?php
var_dump($admin);
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si ya está logueado
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// Proceso login
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    // Permitir login por usuario O email
    $sql = $conn->prepare("
        SELECT id, usuario, email, password 
        FROM admin 
        WHERE usuario = ? OR email = ?
        LIMIT 1
    ");
    $sql->bind_param("ss", $usuario, $usuario);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();

        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id"];
            header("Location: dashboard.php");
            exit;
        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }
    } else {
        $mensaje = "❌ Usuario o email no encontrado.";
    }
}
?>
<link rel="stylesheet" href="admin.css">

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Login | Fidelitipon</title>

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

.btn:hover { background: #2980b9; }

.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 10px;
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
        <input type="text" name="usuario" class="input" placeholder="Usuario o Email" required>
        <input type="password" name="password" class="input" placeholder="Contraseña" required>
        <button class="btn">Entrar</button>
    </form>
</div>

</body>
</html>
