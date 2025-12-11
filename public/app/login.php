<?php
// Debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "DEBUG 1 — login.php cargado<br>";
flush();

// Cargar DB
require_once __DIR__ . '/../../config/db.php';

session_start();
$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "DEBUG 2 — Se ha enviado POST<br>";
    flush();

    $telefono = trim($_POST['telefono'] ?? "");
    $password = trim($_POST['password'] ?? "");

    echo "DEBUG 3 — Telefono: $telefono<br>";
    echo "DEBUG 4 — Password length: " . strlen($password) . "<br>";
    flush();

    if ($telefono === "" || $password === "") {
        $mensaje_error = "Debes introducir teléfono y contraseña.";
    } else {

        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE telefono = ?");
        if (!$stmt) {
            die("ERROR PREPARE: " . $conn->error);
        }

        echo "DEBUG 5 — Consulta preparada<br>";

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
                header("Location: panel_usuario.php");
                exit;
            } else {
                echo "DEBUG 9 — password_verify FALLÓ<br>";
                flush();
                $mensaje_error = "Contraseña incorrecta.";
            }
        } else {
            $mensaje_error = "No existe un usuario con ese teléfono.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<h2>LOGIN</h2>

<?php if ($mensaje_error): ?>
<p style="color:red"><?= $mensaje_error ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Teléfono</label><br>
    <input type="text" name="telefono" required><br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Entrar</button>
</form>

</body>
</html>
