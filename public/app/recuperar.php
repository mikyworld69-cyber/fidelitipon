<?php
require_once __DIR__ . '/../../config/db.php';

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/phpmailer/PHPMailer.php';
require __DIR__ . '/../../vendor/phpmailer/SMTP.php';
require __DIR__ . '/../../vendor/phpmailer/Exception.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"]);

    if ($telefono === "") {
        $mensaje = "Debes introducir tu teléfono.";
    } else {

        // Buscar usuario por teléfono
        $sql = $conn->prepare("SELECT id, email, nombre FROM usuarios WHERE telefono = ?");
        $sql->bind_param("s", $telefono);
        $sql->execute();
        $sql->store_result();

        if ($sql->num_rows === 1) {

            $sql->bind_result($id_user, $email_usuario, $nombre_usuario);
            $sql->fetch();

            // Generar token seguro
            $token = bin2hex(random_bytes(16));

            // Guardarlo en BD
            $up = $conn->prepare("UPDATE usuarios SET token_recuperacion = ? WHERE id = ?");
            $up->bind_param("si", $token, $id_user);
            $up->execute();

            // Enlace que recibirá el usuario
            $enlace = "https://" . $_SERVER['HTTP_HOST'] . "/app/reset.php?token=" . $token;

            // Enviar email con PHPMailer
            $mail = new PHPMailer(true);

            try {
                // CONFIG SMTP — STRATO
                $mail->isSMTP();
                $mail->Host       = 'smtp.strato.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'info@iappsweb.com';   // <-- CAMBIAR
                $mail->Password   = 'Mike91078@MM';         // <-- CAMBIAR
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
                $mail->Port       = 465;                         // SSL

                // Remitente
                $mail->setFrom('info@iappsweb.com', 'Fidelitipon'); // <-- CAMBIAR

                // Destinatario
                $mail->addAddress($email_usuario, $nombre_usuario);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña - Fidelitipon';
                $mail->Body    = "
                    <h2>Hola, $nombre_usuario</h2>
                    <p>Has solicitado restablecer tu contraseña.</p>
                    <p>Haz clic en este enlace:</p>
                    <p><a href='$enlace'>$enlace</a></p>
                    <br>
                    <p>Si no has pedido esto, ignora el mensaje.</p>
                ";

                $mail->send();
                $mensaje = "Hemos enviado un enlace a tu correo electrónico.";

            } catch (Exception $e) {
                $mensaje = "Error enviando email: " . $mail->ErrorInfo;
            }

        } else {
            $mensaje = "No existe ningún usuario con ese teléfono.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
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
    <label>Teléfono</label><br>
    <input type="text" name="telefono" required><br><br>
    <button type="submit">Enviar enlace</button>
</form>

</body>
</html>
