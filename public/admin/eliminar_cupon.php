<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: cupones.php");
    exit;
}

$id = intval($_GET["id"]);

// Obtener QR para borrarlo
$sql = $conn->prepare("SELECT qr_path FROM cupones WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if ($cup) {
    if (!empty($cup["qr_path"])) {
        $file = "/var/data/" . $cup["qr_path"];
        if (file_exists($file)) unlink($file);
    }

    $del = $conn->prepare("DELETE FROM cupones WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
}

header("Location: cupones.php");
exit;
