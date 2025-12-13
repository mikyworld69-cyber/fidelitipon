<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Evitar exportación sin login
if (!isset($_SESSION["admin_id"])) {
    die("No autorizado.");
}

// Forzar descarga CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=cupones_report_" . date("Ymd_His") . ".csv");
echo "\xEF\xBB\xBF"; // UTF-8 BOM

// Construir WHERE igual que en reportes.php
$where = "1";

if (!empty($_GET["comercio"])) {
    $com = intval($_GET["comercio"]);
    $where .= " AND c.comercio_id = $com";
}

if (!empty($_GET["estado"])) {
    $estado = $conn->real_escape_string($_GET["estado"]);
    $where .= " AND c.estado = '$estado'";
}

if (!empty($_GET["desde"])) {
    $desde = $_GET["desde"];
    $where .= " AND c.fecha_caducidad >= '$desde'";
}

if (!empty($_GET["hasta"])) {
    $hasta = $_GET["hasta"];
    $where .= " AND c.fecha_caducidad <= '$hasta'";
}

$sql = "
    SELECT
        c.id,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_caducidad,
        com.nombre AS comercio,
        u.nombre AS usuario
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE $where
    ORDER BY c.id DESC
";

$res = $conn->query($sql);

// CABECERA CSV
echo "ID;Título;Descripción;Estado;Caducidad;Comercio;Usuario\n";

// FILAS
while ($r = $res->fetch_assoc()) {

    $line = [
        $r["id"],
        $r["titulo"],
        $r["descripcion"],
        $r["estado"],
        $r["fecha_caducidad"],
        $r["comercio"] ?: "",
        $r["usuario"] ?: ""
    ];

    // CSV en formato seguro
    echo implode(";", array_map(function($v) {
        return str_replace(["\n", "\r", ";"], [" ", " ", ","], $v);
    }, $line)) . "\n";
}

exit;
