<?php
require_once __DIR__ . '/../../config/db.php';

// Crear tabla admin
$sql = "
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

if (!$conn->query($sql)) {
    die('Error creando tabla admin: ' . $conn->error);
}

// Crear usuario admin por defecto
$usuario = 'admin';
$password = password_hash("admin1234", PASSWORD_DEFAULT);

$sql2 = $conn->prepare("INSERT INTO admin (usuario, password) VALUES (?, ?)");
$sql2->bind_param("ss", $usuario, $password);

if ($sql2->execute()) {
    echo "Administrador creado con éxito.<br>";
    echo "Usuario: admin<br>";
    echo "Contraseña: admin1234<br><br>";
    echo "👉 Ahora puedes borrar este archivo por seguridad.";
} else {
    echo "Error al insertar admin (probablemente ya existe): " . $conn->error;
}
