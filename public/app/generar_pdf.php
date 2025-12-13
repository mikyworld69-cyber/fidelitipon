<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["usuario_id"])) {
    die("Acceso no autorizado.");
}

if (!isset($_GET["id"])) {
    die("ID no recibido.");
}

$cup_id = intval($_GET["id"]);
$user_id = intval($_SESSION["usuario_id"]);

// ============================
// Obtener cupón del usuario
// ============================
$sql = $conn->prepare("
    SELECT 
        c.id,
        c.titulo,
        c.descripcion,
        c.codigo,
        c.estado,
        c.fecha_caducidad,
        c.qr_path,
        com.nombre AS comercio_nombre,
        com.logo AS comercio_logo
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    WHERE c.id = ? AND c.usuario_id = ?
");
$sql->bind_param("ii", $cup_id, $user_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado o no pertenece al usuario.");
}

// ============================
// Obtener casillas
// ============================
$cas = $conn->prepare("
    SELECT numero_casilla, marcada
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$cas->bind_param("i", $cup_id);
$cas->execute();
$casillas = $cas->get_result()->fetch_all(MYSQLI_ASSOC);

// ============================
// Cargar DOMPDF
// ============================
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// ============================
// Construir el HTML del PDF
// ============================
$qrImg = $cup["qr_path"] ? ("/" . $cup["qr_path"]) : "";

$logoHtml = "";
if (!empty($cup["comercio_logo"])) {
    $logoHtml = "<img src='/uploads/comercios/{$cup["comercio_logo"]}' style='width:120px; margin-bottom:20px;'>";
}

$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; }

h1 { text-align:center; }

.qr {
    text-align:center;
    margin-top:20px;
}
.qr img {
    width:220px;
}

.table { 
    width:100%; 
    border-collapse:collapse; 
    margin-top:20px; 
}
.table th, .table td { 
    border:1px solid #666; 
    padding:8px; 
    text-align:center; 
}

.casillas { 
    display:grid; 
    grid-template-columns: repeat(5, 1fr); 
    gap:10px; 
    margin-top:20px; 
}
.casilla {
    padding:12px;
    border:1px solid #444;
    border-radius:6px;
    text-align:center;
    font-size:16px;
    background:#f7f7f7;
}
.casilla.marcada {
    background:#1abc9c;
    color:white;
    font-weight:bold;
}

</style>

<h1>Mi Cupón: {$cup["titulo"]}</h1>

<div style='text-align:center; margin-bottom:20px;'>
    {$logoHtml}
</div>

<div class='qr'>
    <img src='{$qrImg}'>
</div>

<h3>Información del cupón</h3>
<table class='table'>
<tr><th>Código</th><td>{$cup["codigo"]}</td></tr>
<tr><th>Estado</th><td>{$cup["estado"]}</td></tr>
<tr><th>Caduca</th><td>".($cup["fecha_caducidad"] ?: "Sin fecha")."</td></tr>
<tr><th>Comercio</th><td>{$cup["comercio_nombre"]}</td></tr>
</table>

<h3>Casillas</h3>
<div class='casillas'>
";

foreach ($casillas as $c) {
    $cls = $c["marcada"] ? "casilla marcada" : "casilla";
    $html .= "<div class='{$cls}'>{$c["numero_casilla"]}</div>";
}

$html .= "</div>";

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Enviar PDF al navegador
$dompdf->stream("cupon_{$cup_id}.pdf", ["Attachment" => true]);
exit;
