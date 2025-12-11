<?php
// ACTIVAR ERRORES EN DESARROLLO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SIEMPRE iniciar sesión ANTES DE CUALQUIER SALIDA
session_start();

// Cargar la base de datos
require_once __DIR__ . '/../../config/db.php';

$mensaje_error = "";

// PROCESAR LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $telefono = trim($_POST['telefono'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($telefono === "" || $password === "") {
        $mensaje_error = "Debes introducir teléfono y contraseña.";
    } else {

        // Consulta SQL
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            if (password_verify($password, $password_hash)) {

                // Guardar sesión
                $_SESSION['user_id'] = $user_id;

                // Redirigir al panel
                header("Location: panel_usuario.php");
                exit;

            } else {
                $mensaje_error = "Contraseña incorrecta.";
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
</head>
<body>

<h2>Acceso al Panel</h2>

<?php if ($mensaje_error): ?>
<p style="color:red;"><?= $mensaje_error ?></p>
<?php endif; ?>

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
