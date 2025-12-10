<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_POST["email"])) {
    header("Location: recuperar.php?error=Falta email");
    exit;
}

$email = trim($_POST["email"]);

// Buscar usuario
$sql = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    header("Location: recuperar.php?error=Email no encontrado");
    exit;
}

$user = $res->fetch_assoc();

// Generar token
$token = bin2hex(random_bytes(20));
$expira = date("Y-m-d H:i:s", time() + 3600);

// Guardar token
$upd = $conn->prepare("UPDATE usuarios SET token_reset=?, token_expira=? WHERE id=?");
$upd->bind_param("ssi", $token, $expira, $user["id"]);
$upd->execute();

$link = "https://fidelitipon.onrender.com/app/reset.php?token=" . $token;

// ENVIAR EMAIL
$asunto = "Restablecer contraseña | Fidelitipon";
$mensaje = "Hola,\n\nHaz clic aquí para restablecer tu contraseña:\n$link\n\nCaduca en 1 hora.\n";
$headers = "From: no-reply@fidelitipon.com\r\n";

mail($email, $asunto, $mensaje, $headers);

header("Location: recuperar.php?msg=Hemos enviado un email con instrucciones");
exit;
