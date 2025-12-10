<?php
require_once __DIR__ . "/../config/db.php";

$sql = "ALTER TABLE admin ADD COLUMN email VARCHAR(150) NOT NULL AFTER usuario";
if ($conn->query($sql)) {
    echo "Columna email añadida";
} else {
    echo "Error: " . $conn->error;
}
