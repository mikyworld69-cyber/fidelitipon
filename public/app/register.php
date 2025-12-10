<?php
require_once __DIR__ . "/../../config/db.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    // Hash de contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    $sql = $conn->prepare("
        INSERT INTO usuarios (telefono, password, nombre, fecha_registro)
        VALUES (?, ?, ?, NOW())
    ");
    $sql->bind_param("sss", $telefono, $password_hash, $nombre);

    if ($sql->execute()) {
        header("Location: login.php?ok=1");
        exit;
    } else {
        $mensaje = "❌ Error: teléfono ya registrado o fallo en la BD.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro | Fidelitipon</title>

<style>
body {
    background: #f1f1f1;
    font-family: Arial;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.box {
    background: white;
    padding: 25px;
    width: 320px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

h2 {
    text-align: center;
    margin-bottom: 15px;
}

.input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #bbb;
}

.btn {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    background: #27ae60;
    color: white;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #1e8c4d;
}

.error {
    background: #e74c3c;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    color: white;
    text-align: center;
}
</style>

</head>
<body>

<div class="box">
    <h2>Crear Cuenta</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="input" type="text" name="nombre" placeholder="Nombre" required>
        <input class="input" type="text" name="telefono" placeholder="Teléfono" required>
        <input class="input" type="password" name="password" placeholder="Contraseña" required>
        <button class="btn">Registrar</button>
    </form>

    <div style="text-align:center;margin-top:10px;">
        <a href="login.php">Ya tengo cuenta</a>
    </div>
</div>

</body>
</html>
