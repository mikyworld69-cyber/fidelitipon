<?php
session_start();

// Detectar qué tipo de usuario está cerrando sesión
$esAdmin = isset($_SESSION["admin_id"]);
$esUsuario = isset($_SESSION["usuario_id"]);

// Destruir sesión completamente
$_SESSION = [];
session_unset();
session_destroy();

// Redirecciones limpias
if ($esAdmin) {
    header("Location: /admin/login.php");
    exit;
}

if ($esUsuario) {
    header("Location: /app/login.php");
    exit;
}

// fallback
header("Location: /");
exit;
