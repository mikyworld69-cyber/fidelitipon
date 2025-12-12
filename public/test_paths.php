<?php

echo "<h1>PATHS CHECK</h1>";

echo "<p>__DIR__: " . __DIR__ . "</p>";

$path = __DIR__ . '/../lib/phpqrcode/';
echo "<p>Checking: $path</p>";

if (is_dir($path)) {
    echo "<p>✔ La carpeta EXISTE</p>";
    $files = scandir($path);
    echo "<pre>";
    print_r($files);
    echo "</pre>";
} else {
    echo "<p>❌ La carpeta NO existe en el servidor</p>";
}
