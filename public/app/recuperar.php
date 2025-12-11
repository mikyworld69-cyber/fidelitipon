<?php
require_once __DIR__ . '/../../config/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $telefono = trim($_POST["telefono"]);

    if ($telefono === "") {
        $mensaje = "Debes introducir tu número de teléfono.";
    } else {
        // Comprobar si existe
        $sql = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
        $sql->bind_param("s", $telefono);
        $sql->execute();
        $sql->store_result();

        if ($sql->num_rows === 1) {

            $sql->bind_result($id_user);
            $sql->fetch();

            // Generar token
            $token = bin2hex(random_bytes(16));

            // Guardar en BD
            $up = $conn->prepare("UPDATE usuarios SET token_recuperacion = ? WHERE id = ?");
            $up->bind_param("si", $token, $id_user);
            $up->execute();

            // ENVIAR por SMS o email (tú eliges)
            // EJEMPLO SIMPLE — enlace directo:
            $enlace = "https://tudominio.com/app/restablecer.php?token=$token";

            // Aquí puedes integrar tu API SMS o email.
            // Por ahora solo mostramos mensaje.
            $mensaje = "Se ha enviado un enlace de recuperación: $enlace";

        } else {
            $mensaje = "No existe ningún usuario con ese teléfono.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
</head>
<body>

<h2>Recuperar contraseña</h2>

<?php if ($mensaje): ?>
    <p><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST">
    <label>Introduce tu teléfono:</label><br>
    <input type="text" name="telefono" required><br><br>

    <button type="submit">Enviar enlace</button>
</form>

</body>
</html>
