<?php
session_start();

// Cerrar sesión completamente
$_SESSION = [];
session_unset();
session_destroy();

// Evitar que el navegador “recupere” sesión por cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirigir al login
header("Location: login.php");
exit;
