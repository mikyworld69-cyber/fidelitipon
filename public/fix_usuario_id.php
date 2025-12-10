<?php
require_once __DIR__ . "/../config/db.php";

$sql = "ALTER TABLE suscripciones_push MODIFY usuario_id INT NULL";

if ($conn->query($sql)) {
    echo "✔ Campo usuario_id ahora permite NULL";
} else {
    echo "❌ Error: " . $conn->error;
}
