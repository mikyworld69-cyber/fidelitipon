<?php
require_once __DIR__ . '/../../config/db.php';

$queries = [];

/* Tabla ADMIN */
$queries[] = "
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

/* Tabla USUARIOS */
$queries[] = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    telefono VARCHAR(20) UNIQUE,
    password VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

/* Tabla COMERCIOS */
$queries[] = "
CREATE TABLE IF NOT EXISTS comercios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

/* Tabla CUPONES */
$queries[] = "
CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    comercio_id INT NOT NULL,
    titulo VARCHAR(100),
    descripcion TEXT,
    fecha_generado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_caducidad DATE,
    codigo_qr VARCHAR(255) UNIQUE,
    estado ENUM('pendiente','canjeado','caducado') DEFAULT 'pendiente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (comercio_id) REFERENCES comercios(id)
) ENGINE=InnoDB;
";

/* Tabla VALIDACIONES (lector QR) */
$queries[] = "
CREATE TABLE IF NOT EXISTS validaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cupon_id INT NOT NULL,
    comercio_id INT NOT NULL,
    fecha_validado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resultado ENUM('exito','fallo') DEFAULT 'exito',
    FOREIGN KEY (cupon_id) REFERENCES cupones(id),
    FOREIGN KEY (comercio_id) REFERENCES comercios(id)
) ENGINE=InnoDB;
";

/* Tabla NOTIFICACIONES */
$queries[] = "
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200),
    mensaje TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
";

/* Tabla SUSCRIPCIONES PUSH */
$queries[] = "
CREATE TABLE IF NOT EXISTS suscripciones_push (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    endpoint TEXT NOT NULL,
    p256dh TEXT NOT NULL,
    auth TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
";


/* Ejecutar cada tabla */
foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        die("❌ Error ejecutando consulta: " . $conn->error);
    }
}

echo "✔ Todas las tablas han sido creadas correctamente.<br>";
echo "👉 Ahora debes borrar setup_all.php por seguridad.";
