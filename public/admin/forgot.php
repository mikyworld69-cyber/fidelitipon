<?php
require_once __DIR__ . '/../../config/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);

    // Buscar admin por email
    $sql = $conn->prepare("SELECT id FROM admin WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {

        $admin = $res->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", time() + 900); // 15 minutos

        // Guardar token
        $upd = $conn->prepare("UPDATE admin SET reset_token=?, reset_expira=? WHERE id=?");
        $upd->bind_param("ssi", $token, $expira, $admin["id"]);
        $upd->execute();

        // URL de recuperación
        $link = "https://fidelitipon.onrender.com/admin/reset.php?token=" . $token;

        // ENVIAR EMAIL
        $asunto = "Recuperación de contraseña - Fidelitipon";
        $cuerpo = "Hola!\nHaz clic en este enlace para cambiar tu contraseña:\n$link\n\nExpira en 15 minutos.";
        $cabeceras = "From: no-reply@fidelitipon.com\r\n";

        mail($email, $asunto, $cuerpo, $cabeceras);

        $mensaje = "📩 Si el correo existe, se ha enviado un enlace.";
    } else {
        $mensaje = "📩 Si el correo existe, se ha enviado un enlace.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="box">
    <h2>¿Olvidaste tu contraseña?</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" class="input" name="email" placeholder="Correo registrado" required>
        <button class="btn">Enviar enlace</button>
    </form>

    <p style="margin-top:15px; text-align:center;">
        <a href="login.php">Volver al login</a>
    </p>
</div>

</body>
</html>
