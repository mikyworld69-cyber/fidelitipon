<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];

// Obtener cupones del usuario
$sql = $conn->prepare("
    SELECT c.*, com.nombre AS comercio 
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.usuario_id = ?
    ORDER BY c.fecha_creacion DESC
");
$sql->bind_param("i", $user_id);
$sql->execute();
$res = $sql->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Cupones | Fidelitipon</title>
<link rel="stylesheet" href="../assets/css/app.css">
<link rel="stylesheet" href="/public/app/app.css">

<style>
body {
    background: #f4f4f4;
    font-family: Arial;
    margin: 0;
    padding-bottom: 80px;
}

/* Tarjeta moderna */
.card-cupon {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin: 15px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1)
