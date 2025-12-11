<?php
// ACTIVAR ERRORES EN DESARROLLO (puedes quitarlo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión SIEMPRE antes de cualquier salida
session_start();

// Cargar conexión
require_once __DIR__ . '/../../config/db.php';

$mensaje_error = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $telefono = trim($_POST['telefono'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($telefono === "" || $password === "") {
        $mensaje_error = "Debes introducir teléfono y contraseña.";
    } else {

        // Consulta de usuario
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            // Verificación
            if (password_verify($password, $password_hash)) {

                // Guardar sesión correcta
                $_SESSION['user_id'] = $user_id;

                // Redirección al panel
                header("Location: panel_usuario.php");
                exit;

            } else {
                $mensaje_error = "La contraseña es incorrecta.";
            }

        } else {
            $mensaje_error = "No existe un usuario con ese teléfono.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acceso Usuarios</title>
<style>
body { font-family: Arial; background:#f2f2f2; display:flex; justify-content:center; align-items:center; height:100vh; }
.box { background:white; padding:25px; border-radius:8px; width:300px; box-shadow:0 0 12px rgba(0,0,0,0.1); }
input, button { width:100%; padding:10px; margin:10px 0; font-size:15px; }
button { background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#005fcc; }
.error { color:#d00; text-align:center; }
</style>
</head>

<body>

<div class="box">
    <h2 style="text-align:center;">Acceder</h2>

    <?php if ($mensaje_error): ?>
        <p class="error"><?= $mensaje_error ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

    <div style="text-align:center;">
        <a href="recuperar.php">¿Has olvidado tu contraseña?</a>
    </div>
</div>

</body>
</html>
