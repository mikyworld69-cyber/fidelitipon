<?php
require_once __DIR__ . '/../../config/db.php';

echo "<h2>BASE DE DATOS ACTUAL:</h2>";
$r = $conn->query("SELECT DATABASE() AS db");
echo "👉 BD activa: <b>" . $r->fetch_assoc()["db"] . "</b><br><br>";

echo "<h2>CONSULTA REAL:</h2>";
$result = $conn->query("SELECT * FROM admins WHERE id=1");
$row = $result->fetch_assoc();

echo "<pre>";
print_r($row);
echo "</pre>";

echo "<h2>HASH EXACTO (con longitud):</h2>";
$hash = $row["password"];
echo "HASH: [" . $hash . "]<br>";
echo "LONGITUD: " . strlen($hash) . "<br><br>";

echo "<h3>CARACTERES ASCII:</h3>";
for ($i = 0; $i < strlen($hash); $i++) {
    echo $i . " → " . ord($hash[$i]) . "<br>";
}
