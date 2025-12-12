<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

// =============================================
// VALIDACIÓN DE SESIÓN ADMIN
// =============================================
if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No autorizado"]);
    exit;
}

// =============================================
// RECIBIR CÓDIGO DEL QR
// =============================================
if (!isset($_GET["codigo"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Código no recibido"]);
    exit;
}

$codigo = trim($_GET["codigo"]);

// =============================================
// 1. BUSCAR CUPÓN
// =============================================
$sql = $conn->prepare("
    SELECT id, estado, fecha_caducidad, comercio_id, usuario_id 
    FROM cupones
    WHERE codigo = ?
    LIMIT 1
");
$sql->bind_param("s", $codigo);
$sql->execute();
$res = $sql->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón no encontrado"]);
    exit;
}

$cupon = $res->fetch_assoc();
$cupon_id = $cupon["id"];

// =============================================
// 2. VALIDAR CADUCADO
// =============================================
if (!empty($cupon["fecha_caducidad"]) && strtotime($cupon["fecha_caducidad"]) < time()) {
    echo json_encode(["status" => "CADUCADO", "mensaje" => "Cupón caducado"]);
    exit;
}

// =============================================
// 3. VALIDAR ESTADO
// =============================================
if ($cupon["estado"] === "usado") {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón ya ha sido utilizado completamente"]);
    exit;
}

// =============================================
// 4. BUSCAR LA PRÓXIMA CASILLA NO MARCADA
// =============================================
$sqlCas = $conn->prepare("
    SELECT id, numero_casilla
    FROM cupon_casillas
    WHERE cupon_id = ? AND marcada = 0
    ORDER BY numero_casilla ASC
    LIMIT 1
");
$sqlCas->bind_param("i", $cupon_id);
$sqlCas->execute();
$cas = $sqlCas->get_result()->fetch_assoc();

if (!$cas) {
    // Si no hay casillas libres → cupón completado
    $conn->query("UPDATE cupones SET estado='usado' WHERE id=$cupon_id");
    echo json_encode(["status" => "COMPLETO", "mensaje" => "Cupón ya completado"]);
    exit;
}

$casilla_id = $cas["id"];
$numero_casilla = $cas["numero_casilla"];

// =============================================
// 5. MARCAR CASILLA
// =============================================
$now = date("Y-m-d H:i:s");

$updCas = $conn->prepare("
    UPDATE cupon_casillas 
    SET marcada = 1, fecha_marcada = ?
    WHERE id = ?
");
$updCas->bind_param("si", $now, $casilla_id);
$updCas->execute();

// =============================================
// 6. REGISTRAR VALIDACIÓN
// =============================================
$metodo = "QR";

$ins = $conn->prepare("
    INSERT INTO validaciones (cupon_id, casilla, fecha_validacion, metodo)
    VALUES (?, ?, ?, ?)
");
$ins->bind_param("iiss", $cupon_id, $numero_casilla, $now, $metodo);
$ins->execute();

// =============================================
// 7. SI YA NO QUEDAN CASILLAS → MARCAR CUPÓN COMPLETADO
// =============================================
$check = $conn->query("
    SELECT COUNT(*) AS faltan 
    FROM cupon_casillas 
    WHERE cupon_id = $cupon_id AND marcada = 0
")->fetch_assoc();

if ($check["faltan"] == 0) {
    $conn->query("UPDATE cupones SET estado='usado' WHERE id=$cupon_id");

    echo json_encode([
        "status" => "COMPLETADO",
        "mensaje" => "Última casilla marcada. Cupón finalizado.",
        "casilla" => $numero_casilla
    ]);
    exit;
}

// =============================================
// 8. RESPUESTA OK
// =============================================
echo json_encode([
    "status" => "OK",
    "mensaje" => "Casilla marcada correctamente",
    "casilla" => $numero_casilla,
    "fecha" => $now
]);
exit;
