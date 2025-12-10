<?php
require_once __DIR__ . "/../config/db.php";

$sql = "ALTER TABLE usuarios 
        ADD fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";

if ($conn->query($sql) === TRUE) {
    echo "✔ COLUMNA CREADA CORRECTAMENTE";
} else {
    echo "❌ ERROR: " . $conn->error;
}
