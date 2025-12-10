<?php
require_once __DIR__ . "/../../config/db.php";

$token = $_GET["token"] ?? "";
$mensaje = "";

$sql = $conn->prepare("SELECT id FROM admin WHERE reset_token = ? AND reset_expira > NOW()");
$sql->bind_param("s", $token);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows !== 1) {
    die("Enlace inválido o expirado.");
}

$admin = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $upd = $conn->prepare("UPDATE admin SET password = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
    $upd->bind_param("si", $pass, $admin["id"]);
    $upd->execute();

    echo "Contraseña actualizada. Ya puedes iniciar sesión.";
    exit;
}
?>

<form method="POST">
    <input type="password" name="password" placeholder="Nueva contraseña" required>
    <button class="btn">Actualizar</button>
</form>
