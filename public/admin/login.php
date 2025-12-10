<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si ya está logueado → entra directo
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// PROCESO DE LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Buscar admin por email
    $sql = $conn->prepare("SELECT id, usuario, email, password FROM admin WHERE email = ?");
    $sql->bind_param("s", $email);
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
        $mensaje = "❌ No existe una cuenta con este correo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login Admin</title>

<style>
body {
    background: #ecf0f1;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial, sans-serif;
}

.box {
    width: 350px;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

.input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #bbb;
    font-size: 16px;
}

.btn {
    width: 100%;
    padding: 12px;
    background: #3498db;
    border: none;
    color: white;
    font-size: 17px;
    border-radius: 8px;
    cursor: pointer;
}

.btn:hover {
    background: #2980b9;
}

.error {
    background: #e74c3c;
    padding: 12px;
    color: white;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}
</style>

</head>
<body>

<div class="box">
    <h2>Panel Admin</h2>

    <?php if (!empty($mensaje)): ?>
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
