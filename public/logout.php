<?php
session_start();

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión completamente
session_destroy();

// Redirigir al login
header("Location: login.php");
exit;
