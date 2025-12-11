<?php
// ACTIVAR ERRORES
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SIEMPRE INICIAR SESIÓN ANTES DE CUALQUIER OUTPUT
session_start();

// Debug inicial
echo "DEBUG 1 — login.php cargado<br>";
flush();

// Cargar la base de datos (ruta correcta según tu estructura)
require_once __DIR__ . '/../../config/db.php';

$mensaje_error = "";

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    echo "DEBUG 2 — Se ha enviado POST<br>";
    flush();

    $telefono = trim($_POST['telefono'] ?? "");
    $password = trim($_POST['password'] ?? "");

    echo "DEBUG 3 — Teléfono recibido: $telefono<br>";
    echo "DEBUG 4 — Password length: " . strlen($password) . "<br>";
    flush();

    if ($telefono === "" || $password === "") {
        $mensaje_error = "Debes introducir teléfono y contraseña.";
    } else {

        // Consulta SQL
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
        if (!$stmt) {
            die("ERROR PREPARE: " . $conn->error);
        }

        echo "DEBUG 5 — Prepare OK<br>";
        flush();

        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $stmt->store_result();

        echo "DEBUG 6 — Num rows = {$stmt->num_rows}<br>";
        flush();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            echo "DEBUG 7 — Hash encontrado: $password_hash<br>";
            flush();

            if (password_verify($password, $password_hash)) {
                echo "DEBUG 8 — password_verify OK<br>";
                flush();

                $_SESSION['user_id'] = $user_id;

                echo "DEBUG 9 — Redirigiendo al panel<br>";
                flush();

                header("Location: panel_usuario.php");
                exit;

            } else {
                echo "DEBUG 10 — password_verify FALLÓ<br>";
                flush();
                $mensaje_error = "Contraseña incorrecta.";
            }

        } else {
            echo "DEBUG 11 — Usuario no encontrado<br>";
            flush();
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
</head>
<body>

<h2>Acceso al Panel</h2>

<?php if ($mensaje_error): ?>
<p style="color:red;"><?= $mensaje_error ?></p>
<?php endif; ?>

<!-- FORMULARIO CORRECTO -->
<form method="POST" action="login.php">
    <label>Teléfono:</label><br>
    <input type="text" name="telefono" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Entrar</button>
</form>

<br>
<a href="recuperar.php">¿Has olvidado tu contraseña?</a>

</body>
</html>
