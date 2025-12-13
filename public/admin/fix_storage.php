<?php
echo "<pre>=== FIX STORAGE ===\n";

// Directorios en disco persistente
$persist = "/var/data/uploads";
$dirs = [
    "comercios",
    "qrs"
];

// Crear base persistente si no existe
if (!is_dir($persist)) {
    mkdir($persist, 0775, true);
    echo "Creado: $persist\n";
}

// Crear subdirectorios persistentes
foreach ($dirs as $d) {
    $p = "$persist/$d";
    if (!is_dir($p)) {
        mkdir($p, 0775, true);
        echo "Creado dir persistente: $p\n";
    }
}


// Ruta pública donde deben ir los symlinks
$publicUploads = "/var/www/public/uploads";

if (!is_dir($publicUploads)) {
    mkdir($publicUploads, 0775, true);
    echo "Creado directorio público: $publicUploads\n";
}

foreach ($dirs as $d) {
    $link = "$publicUploads/$d";

    // Si existe como carpeta → eliminar
    if (is_dir($link) && !is_link($link)) {
        echo "Eliminando carpeta física duplicada: $link\n";
        exec("rm -rf $link");
    }

    // Si existe un symlink roto → eliminar
    if (is_link($link) && !file_exists($link)) {
        echo "Eliminando symlink roto: $link\n";
        unlink($link);
    }

    // Crear symlink si no existe
    if (!file_exists($link)) {
        symlink("$persist/$d", $link);
        echo "Symlink creado: $link → $persist/$d\n";
    }
}


// Ajustar permisos
exec("chown -R www-data:www-data $persist");
exec("chmod -R 775 $persist");

echo "\n✔ FIX COMPLETADO\n</pre>";
