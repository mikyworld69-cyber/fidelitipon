<?php
require_once __DIR__ . '/../../config/db.php';

echo "<h2>BASE DE DATOS ACTUAL:</h2>";
$r = $conn->query("SELECT DATABASE() AS db");
echo "👉 BD activa: <b>" . $r->fetch_assoc()["db"] . "</b><br><br>";

echo "<h2>TABLAS QUE VE RENDER:</h2>";
$tables = $conn->query("SHOW TABLES;");
while ($t = $tables->fetch_array()) {
    echo "- " . $t[0] . "<br>";
}

echo "<hr>";

echo "<h2>CONTENIDO DE LA TABLA admins:</h2>";
$check = $conn->query("SELECT id, usuario, email, password FROM admins");
while ($row = $check->fetch_assoc()) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
}

echo "<hr><h2>FIN TEST</h2>";
