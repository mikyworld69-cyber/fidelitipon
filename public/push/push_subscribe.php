<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION["usuario_id"])) exit;

$endpoint = $data["endpoint"];
$auth     = $data["keys"]["auth"];
$p256dh   = $data["keys"]["p256dh"];
$user_id  = $_SESSION["usuario_id"];

$sql = $conn->prepare("
    INSERT INTO suscripciones_push (usuario_id, endpoint, keys_auth, keys_p256dh)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE endpoint = VALUES(endpoint)
");
$sql->bind_param("isss", $user_id, $endpoint, $auth, $p256dh);
$sql->execute();
