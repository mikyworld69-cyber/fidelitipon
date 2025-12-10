<?php
require_once __DIR__ . "/../../config/db.php";

// ======= CONFIGURACIÓN =======
$usuario = "admin";
$nuevoHash = "$2y$10$KZ2o8yQ9htOeX6HIkPlPGe1lOYCK5zx9wYgpwd3ZO/Z07JYiMPLUm";

// ======= ACTUALIZAR CONTRASEÑA =======
$sql = $conn->prepare("UPDATE admin SET password = ? WHERE usuario = ?");
$sql->bind_param("ss", $nuevoHash, $usuario);

if ($sql->execute()) {
    echo "<h2>✔ Contraseña de ADMIN actualizada correctamente</h2>";
    echo "<p><strong>Usuario:</strong> admin</p>";
    echo "<p><strong>Nueva contraseña:</strong> admin1234</p>";
    echo "<p>💡 Recuerda borrar este archivo cuando termines.</p>";
} else {
    echo "<h2>❌ Error al actualizar contraseña</h2>";
    echo $conn->error;
}
