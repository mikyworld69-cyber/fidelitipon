<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar conexión a la BD (ruta correcta según tu estructura)
require_once __DIR__ . '/../../config/db.php';

// Iniciamos sesión
session_start();

$mensaje_error = "";

// Si llega el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibir datos del formulario
    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($email === "" || $password === "") {
        $mensaje_error = "Debes introducir email y contraseña.";
    } else {
        // Preparamos consulta
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE email = ?");
        if (!$stmt) {
            die("Error en prepare(): " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Comprobamos si existe el usuario
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            // Verificar contraseña
            if (password_verify($password, $password_hash)) {

                // Guardar sesión
                $_SESSION['user_id'] = $user_id;

                // Redirigir al panel de usuario
                header("Location: ../usuario/panel_usuario.php");
                exit;

            } else {
                $mensaje_error = "La contraseña no es correcta.";
            }
        } else {
            $mensaje_error = "No existe un usuario con ese email.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login de Usuario</title>
</head>
<body>

<h2>Acceder al Panel de Usuario</h2>

<?php if ($mensaje_error !== ""): ?>
    <p style="color:red;"><?= htmlspecialchars($mensaje_error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Acceder</button>
</form>

</body>
</html>
