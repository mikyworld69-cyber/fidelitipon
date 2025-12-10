<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_GET["token"])) {
    die("Token no válido.");
}

$token = $_GET["token"];

$sql = $conn->prepare("SELECT id, token_expira FROM usuarios WHERE token_reset = ?");
$sql->bind_param("s", $token);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    die("Token inválido o expirado.");
}

$user = $res->fetch_assoc();

if (strtotime($user["token_expira"]) < time()) {
    die("El enlace ha expirado. Solicita uno nuevo.");
}

require_once __DIR__ . '/../head_universal.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
</head>
<body>

<div class="contenedor-app">
    <h2 class="titulo-app">Nueva contraseña</h2>

    <form action="procesar_reset.php" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label>Nueva contraseña</label>
        <input type="password" name="password" required minlength="5">

        <button class="btn-primario" type="submit">Guardar</button>
    </form>
</div>

</body>
</html>
