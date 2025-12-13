<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    die("No autorizado.");
}

// Forzar descarga CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=validaciones_" . date("Ymd_His") . ".csv");
echo "\xEF\xBB\xBF"; // UTF-8 BOM

$sql = "
    SELECT 
        v.id,
        c.codigo,
        c.titulo,
        com.nombre AS comercio,
        v.fecha_validacion,
        v.metodo
    FROM validaciones v
    LEFT JOIN cupones c ON v.cupon_id = c.id
    LEFT JOIN comercios com ON v.comercio_id = com.id
    ORDER BY v.id DESC
";

$res = $conn->query($sql);

// CABECERA
echo "ID Validación;Código;Título;Comercio;Fecha Validación;Método\n";

while ($r = $res->fetch_assoc()) {

    $line = [
        $r["id"],
        $r["codigo"],
        $r["titulo"],
        $r["comercio"],
        $r["fecha_validacion"],
        $r["metodo"]
    ];

    echo implode(";", array_map(function($v) {
        return str_replace(["\n", "\r", ";"], [" ", " ", ","], $v);
    }, $line)) . "\n";
}

exit;
