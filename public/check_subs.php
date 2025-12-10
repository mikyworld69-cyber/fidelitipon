<?php
require_once __DIR__ . "/../config/db.php";

$res = $conn->query("SELECT * FROM suscripciones_push ORDER BY id DESC LIMIT 20");

echo "<pre>";
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
echo "</pre>";
