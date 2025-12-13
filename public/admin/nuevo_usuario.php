<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);

    if ($nombre === "" || $telefono === "" || $password === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } else {

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = $conn->prepare("
            INSERT INTO usuarios (nombre, telefono, password)
            VALUES (?, ?, ?)
        ");

        $sql->bind_param("sss", $nombre, $telefono, $hash);

        if ($sql->execute()) {
            header("Location: usuarios.php");
            exit;
        } else {
            $mensaje = "Error al crear usuario.";
        }
    }
}

include "_header.php";
?>

<h1>Crear Usuario</h1>

<div class="card" style="max-width:450px; margin:auto;">

<?php if ($mensaje): ?>
    <div class="error" style="background:#c0392b;color:white;padding:10px;border-radius:6px;margin-bottom:15px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<form method="POST">

    <label>Nombre *</label>
    <input type="text" name="nombre" required>

    <label>Teléfono *</label>
    <input type="text" name="telefono" required>

    <label>Contraseña *</label>
    <input type="password" name="password" required>

    <button class="btn-success" style="margin-top:15px;">Crear Usuario</button>

</form>

</div>

<?php include "_footer.php"; ?>
