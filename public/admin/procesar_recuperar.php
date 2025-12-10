<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_POST["email"])) {
    header("Location: recuperar.php?error=Falta el email");
    exit;
}

$email = trim($_POST["email"]);

// Buscar admin
$sql = $conn->prepare("SELECT id FROM admin WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    header("Location: recuperar.php?error=Email no encontrado");
    exit;
}

$admin = $res->fetch_assoc();
$token = bin2hex(random_bytes(20));
$expira = date("Y-m-d H:i:s", time() + 3600); // 1 hora

// Guardar token
$upd = $conn->prepare("UPDATE admin SET token_reset=?, token_expira=? WHERE id=?");
$upd->bind_param("ssi", $token, $expira, $admin["id"]);
$upd->execute();

// Crear link
$resetLink = "https://fidelitipon.onrender.com/admin/reset.php?token=" . $token;

// ENVIAR EMAIL
$asunto = "Recuperar contraseña | Fidelitipon";
$mensaje = "Hola,\n\nHaz clic en el siguiente enlace para restablecer tu contraseña:\n\n$resetLink\n\nEste enlace caduca en 1 hora.\n\n";

$headers = "From: no-reply@fidelitipon.com\r\n";

mail($email, $asunto, $mensaje, $headers);

header("Location: recuperar.php?msg=Hemos enviado un correo con instrucciones");
exit;
