<?php
require_once __DIR__ . '/../../config/db.php';

$newPass = "FidelitiPON2025";
$hash = password_hash($newPass, PASSWORD_BCRYPT);

echo "<h3>HASH GENERADO:</h3>";
echo $hash . "<br><br>";

$q = $conn->prepare("UPDATE admins SET password=? WHERE id=1");
$q->bind_param("s", $hash);
$q->execute();

echo "Contraseña actualizada correctamente.";
