<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$comercio_id = intval($_GET["id"]);

// 1. Obtener información del comercio (para saber si tiene logo)
$sql = $conn->prepare("SELECT logo FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$res = $sql->get_result();
$comercio = $res->fetch_assoc();

if (!$comercio) {
    header("Location: comercios.php?error=notfound");
    exit;
}

// 2. Eliminar logo si existe en el servidor
if (!empty($comercio["logo"])) {
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/" . $comercio["logo"];

    if (file_exists($logoPath)) {
        unlink($logoPath);
    }
}

// 3. Eliminar el comercio
$del = $conn->prepare("DELETE FROM comercios WHERE id = ?");
$del->bind_param("i", $comercio_id);
$del->execute();

// 4. Redirigir con mensaje de éxito
header("Location: comercios.php?deleted=1");
exit;
