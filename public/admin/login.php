<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si ya está logueado → entra directo
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// PROCESO LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $sql = $conn->prepare("SELECT id, password FROM admin WHERE email = ?");
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
        $mensaje = "❌ No existe ninguna cuenta con ese email.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acceso Admin | Fidelitipon</title>

<style>
/* FONDO */
body {
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #3498db, #9b59b6);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: "Roboto", sans-serif;
}

/* CONTENEDOR */
.login-box {
    width: 350px;
    background: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    animation: fadeIn 0.6s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* TÍTULO */
.login-box h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #2c3e50;
}

/* INPUTS */
.input {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    transition: 0.2s;
}

.input:focus {
    border-color: #3498db;
    outline: none;
}

/* BOTÓN */
.btn {
    width: 100%;
    padding: 14px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: 0.2s;
}

.btn:hover {
    background: #2980b9;
}

/* ERROR */
.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 15px;
}

/* ENLACES */
.link {
    text-align: center;
    margin-top: 15px;
}
.link a {
    color: #3498db;
    text-decoration: none;
}
</style>

</head>
<body>

<div class="login-box">

    <h2>Panel Admin</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" class="input" placeholder="Correo electrónico" required>
        <input type="password" name="password" class="input" placeholder="Contraseña" required>
        <button class="btn">Entrar</button>
    </form>

    <div class="link">
        <a href="recuperar.php">¿Has olvidado tu contraseña?</a>
    </div>
</div>

</body>
</html>
