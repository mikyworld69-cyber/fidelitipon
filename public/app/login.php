<?php
// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ruta correcta a db.php
require_once __DIR__ . '/../../config/db.php';

session_start();

$mensaje_error = "";

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $telefono = trim($_POST['telefono'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($telefono === "" || $password === "") {
        $mensaje_error = "Debes introducir tu teléfono y contraseña.";
    } else {

        // Buscar usuario por teléfono
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
        if (!$stmt) {
            die("Error en prepare(): " . $conn->error);
        }

        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            if (password_verify($password, $password_hash)) {

                $_SESSION['user_id'] = $user_id;

                header("Location: panel_usuario.php");
                exit;

            } else {
                $mensaje_error = "La contraseña es incorrecta.";
            }
        } else {
            $mensaje_error = "No existe un usuario con ese número de teléfono.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Usuario</title>

    <!-- ESTILOS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .login-box {
            background:white;
            padding:30px;
            border-radius:10px;
            width:320px;
            box-shadow:0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align:center;
            margin-bottom:20px;
        }
        input[type="text"],
        input[type="password"] {
            width:100%;
            padding:10px;
            margin:10px 0;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:15px;
        }
        button {
            width:100%;
            padding:12px;
            background:#0075ff;
            color:white;
            border:none;
            border-radius:6px;
            font-size:16px;
            cursor:pointer;
        }
        button:hover {
            background:#005cd1;
        }
        .error {
            color:#d40000;
            margin-bottom:10px;
            text-align:center;
        }
        .recuperar {
            margin-top:10px;
            text-align:center;
        }
        .recuperar a {
            color:#0075ff;
            text-decoration:none;
        }
        .recuperar a:hover {
            text-decoration:underline;
        }
    </style>

</head>
<body>

<div class="login-box">
    <h2>Acceder</h2>

    <?php if ($mensaje_error !== ""): ?>
        <p class="error"><?= htmlspecialchars($mensaje_error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Teléfono:</label>
        <input type="text" name="telefono" placeholder="Ej: 612345678" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>

    <div class="recuperar">
        <a href="recuperar.php">¿Has olvidado tu contraseña?</a>
    </div>
</div>

</body>
</html>
