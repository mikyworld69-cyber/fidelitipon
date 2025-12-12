<?php
echo "<h2>TEST DE RUTAS EN RENDER</h2>";

$path = __DIR__ . "/../../public/uploads/comercios/";

echo "<p><strong>Ruta absoluta esperada:</strong><br>$path</p>";

if (is_dir($path)) {
    echo "<p style='color:green;'>✔ La carpeta EXISTE en Render</p>";
} else {
    echo "<p style='color:red;'>❌ La carpeta NO existe en Render</p>";
}

echo "<p>Contenido de la carpeta (si existe):</p>";

$files = @scandir($path);
var_dump($files);
