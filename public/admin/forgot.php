<?php
require_once __DIR__ . "/../../config/db.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Buscar admin
    $sql = $conn->prepare("SELECT id FROM admin WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        $token = bin2hex(random_bytes(32));

        // Guardar token
        $save = $conn->prepare("UPDATE admin SET reset_token = ?, reset_expira = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
        $save->bind_param("si", $token, $admin["id"]);
        $save->execute();

        // Enlace
        $link = "https://fidelitipon.onrender.com/admin/reset.php?token=" . $token;

        // Enviar correo
        mail($email, "Recuperar contraseña Fidelitipon", "Haz clic para recuperar tu contraseña:\n\n" . $link);

        $mensaje = "Correo enviado. Revisa tu bandeja.";
    } else {
        $mensaje = "No existe un admin con ese correo.";
    }
}
?>
<form method="POST">
    <input type="email" name="email" placeholder="Tu correo" required>
    <button class="btn">Enviar enlace</button>
</form>
<p><?= $mensaje ?></p>
