<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No autorizado"]);
    exit;
}

if (!isset($_GET["codigo"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Código no recibido"]);
    exit;
}

$codigo = trim($_GET["codigo"]);

// Buscar cupón por código QR
$sql = $conn->prepare("
    SELECT id, estado, fecha_caducidad, comercio_id
    FROM cupones
    WHERE codigo = ?
");
$sql->bind_param("s", $codigo);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón no encontrado"]);
    exit;
}

$cupon = $result->fetch_assoc();

// Cupón caducado
if (!empty($cupon["fecha_caducidad"]) && strtotime($cupon["fecha_caducidad"]) < time()) {

    $update = $conn->prepare("UPDATE cupones SET estado='caducado' WHERE id=?");
    $update->bind_param("i", $cupon["id"]);
    $update->execute();

    echo json_encode(["status" => "CADUCADO", "mensaje" => "Cupón caducado"]);
    exit;
}

// Si ya está usado
if ($cupon["estado"] === "usado") {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón ya canjeado"]);
    exit;
}

// Buscar la siguiente casilla disponible
$cas = $conn->prepare("
    SELECT id, numero_casilla 
    FROM cupon_casillas
    WHERE cupon_id = ? AND marcada = 0
    ORDER BY numero_casilla ASC LIMIT 1
");
$cas->bind_param("i", $cupon["id"]);
$cas->execute();
$resCas = $cas->get_result();

if ($resCas->num_rows === 0) {
    // No hay casillas → cupón completado previamente
    echo json_encode(["status" => "INFO", "mensaje" => "Cupón ya está completo"]);
    exit;
}

$casilla = $resCas->fetch_assoc();

// Marcar casilla
$upd = $conn->prepare("
    UPDATE cupon_casillas 
    SET marcada = 1, fecha_marcada = NOW(), comercio_id = ?
    WHERE id = ?
");
$upd->bind_param("ii", $_SESSION["admin_id"], $casilla["id"]);
$upd->execute();

// Registrar validación
$reg = $conn->prepare("
    INSERT INTO validaciones (cupon_id, casilla, comercio_id, metodo)
    VALUES (?, ?, ?, 'QR')
");
$reg->bind_param("iii", $cupon["id"], $casilla["numero_casilla"], $_SESSION["admin_id"]);
$reg->execute();

// Verificar si queda alguna casilla sin marcar
$check = $conn->prepare("
    SELECT COUNT(*) AS faltan
    FROM cupon_casillas
    WHERE cupon_id = ? AND marcada = 0
");
$check->bind_param("i", $cupon["id"]);
$check->execute();
$rest = $check->get_result()->fetch_assoc();

if ($rest["faltan"] == 0) {
    $fin = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id = ?");
    $fin->bind_param("i", $cupon["id"]);
    $fin->execute();

    echo json_encode([
        "status" => "COMPLETADO",
        "mensaje" => "Cupón completo. Cupón marcado como usado.",
        "casilla" => $casilla["numero_casilla"]
    ]);
    exit;
}

// Respuesta normal
echo json_encode([
    "status" => "OK",
    "casilla" => $casilla["numero_casilla"],
    "mensaje" => "Casilla marcada correctamente"
]);
exit;
