<?php
$hostname = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname   = getenv('DB_NAME');

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
