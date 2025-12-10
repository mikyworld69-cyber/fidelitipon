<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_POST["token"]) || !isset($_POST["password"])) {
    die("Error en envío.");
}

$token = $_POST["token"];
$pass = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Buscar usuario
$sql = $conn->prepare("SELECT id FROM usuarios WHERE token_reset = ?");
$sql->bind_param("s", $token);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    die("Token inválido.");
}

$user = $res->fetch_assoc();

// Actualizar contraseña
$upd = $conn->prepare("UPDATE usuarios SET password=?, token_reset=NULL, token_expira=NULL WHERE id=?");
$upd->bind_param("si", $pass, $user["id"]);
$upd->execute();

header("Location: login.php?msg=Contraseña actualizada correctamente");
exit;
