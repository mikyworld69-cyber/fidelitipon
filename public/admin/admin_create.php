<?php
require_once __DIR__ . '/../../config/db.php';

$password = password_hash("admin1234", PASSWORD_DEFAULT);

$sql = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    password VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (nombre, email, telefono, password)
VALUES ('Administrador', 'admin@fidelitipon.com', '600000000', '$password');
";

if ($conn->multi_query($sql)) {
    echo "Administrador creado con éxito.<br>";
    echo "Usuario: admin@fidelitipon.com<br>";
    echo "Contraseña: admin1234";
} else {
    echo "Error: " . $conn->error;
}
