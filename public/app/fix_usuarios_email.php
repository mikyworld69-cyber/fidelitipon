<?php
require_once __DIR__ . "/../../config/db.php";

$sql = "ALTER TABLE usuarios ADD COLUMN email VARCHAR(150) NOT NULL AFTER telefono";

if ($conn->query($sql)) {
    echo "Columna email añadida correctamente ✔";
} else {
    echo "Error: " . $conn->error;
}
