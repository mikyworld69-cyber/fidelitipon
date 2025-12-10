<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Si ya está logueado → redirigir
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel_usuario.php");
    exit;
}

$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);
    $nombre   = trim($_POST["nombre"]);

    // Validación básica
    if ($telefono === "" || $password === "") {
        $mensaje = "❌ Todos los campos obligatorios deben estar llenos.";
    } else {

        // ¿Existe ya el teléfono?
        $check = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
        $check->bind_param("s", $telefono);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $mensaje = "❌ Este teléfono ya está registrado.";
        } else {

            // Guardar contraseña con hash
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // INSERT seguro: solo columnas que sabemos que existen
            $insert = $conn->prepare("
                INSERT INTO usuarios (telefono, password, nombre)
                VALUES (?, ?, ?)
            ");
            $insert->bind_param("sss", $telefono, $passwordHash, $nombre);

            if ($insert->execute()) {

                // Autologin
                $_SESSION["usuario_id"] = $insert->insert_id;

                header("Location: panel_usuario.php");
                exit;

            } else {
                $mensaje = "❌ Error al registrar usuario: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Cuenta | Fidelitipon</title>

<style>
body {
    margin: 0;
    padding: 0;
    background: #eef2f7;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.register-box {
    width: 330px;
    background: white;
    padding: 30px 25px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    text-align: center;
}

.icon {
    font-size: 60px;
    margin-bottom: 10px;
    color: #27ae60;
}

h2 {
    margin-bottom: 25px;
    font-size: 24px;
    color: #2c3e50;
}

.input {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    font-size: 15px;
}

.btn {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    background: #27ae60;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.btn:hover {
    background: #1f8a4a;
}

.error {
    background: #e74c3c;
    color: white;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 15px;
    font-size: 14px;
}

.register-link {
    margin-top: 12px;
    font-size: 14px;
}

.register-link a {
    color: #3498db;
    text-decoration: none;
}
.register-link a:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="register-box">
    <div class="icon">📝</div>
    <h2>Crear Cuenta</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="input" type="text" name="telefono" placeholder="Teléfono" required>
        <input class="input" type="password" name="password" placeholder="Contraseña" required>
        <input class="input" type="text" name="nombre" placeholder="Nombre (opcional)">
        <button class="btn">Crear cuenta</button>
    </form>

    <div class="register-link">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </div>
</div>

</body>
</html>
