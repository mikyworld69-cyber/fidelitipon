<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';

// ===============================
// 1. Validación de sesión admin
// ===============================
if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No autorizado."]);
    exit;
}

// ===============================
// 2. Validación de parámetro
// ===============================
if (!isset($_GET["codigo"])) {
    echo json_encode(["status" => "ERROR", "mensaje" => "No se recibió ningún código."]);
    exit;
}

$codigo = trim($_GET["codigo"]);


// ===============================
// 3. Buscar cupón
// ===============================
$sql = $conn->prepare("
    SELECT id, usuario_id, comercio_id, estado, fecha_caducidad, total_casillas
    FROM cupones
    WHERE id = ?
    LIMIT 1
");
$sql->bind_param("i", $codigo);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    echo json_encode(["status" => "ERROR", "mensaje" => "Cupón no encontrado."]);
    exit;
}


// ===============================
// 4. Validar caducidad
// ===============================
if (!empty($cup["fecha_caducidad"]) && strtotime($cup["fecha_caducidad"]) < time()) {
    echo json_encode(["status" => "CADUCADO", "mensaje" => "El cupón está caducado."]);
    exit;
}


// ===============================
// 5. Contar casillas marcadas
// ===============================
$q1 = $conn->prepare("
    SELECT COUNT(*) AS usadas
    FROM cupon_casillas
    WHERE cupon_id = ? AND estado = 1
");
$q1->bind_param("i", $cup["id"]);
$q1->execute();
$usadas = $q1->get_result()->fetch_assoc()["usadas"];


// Si ya completó todas
if ($usadas >= $cup["total_casillas"]) {

    // Aseguramos estado "usado"
    $upd = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
    $upd->bind_param("i", $cup["id"]);
    $upd->execute();

    echo json_encode([
        "status" => "COMPLETO",
        "mensaje" => "El cupón ya estaba COMPLETADO. No hay casillas disponibles."
    ]);
    exit;
}


// ===============================
// 6. Buscar primera casilla libre
// ===============================
$q2 = $conn->prepare("
    SELECT id, numero_casilla
    FROM cupon_casillas
    WHERE cupon_id = ? AND estado = 0
    ORDER BY numero_casilla ASC
    LIMIT 1
");
$q2->bind_param("i", $cup["id"]);
$q2->execute();
$casilla = $q2->get_result()->fetch_assoc();

if (!$casilla) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => "No hay casillas disponibles."
    ]);
    exit;
}


// ===============================
// 7. Marcar casilla
// ===============================
$now = date("Y-m-d H:i:s");

$updC = $conn->prepare("
    UPDATE cupon_casillas
    SET estado = 1, fecha_marcado = ?
    WHERE id = ?
");
$updC->bind_param("si", $now, $casilla["id"]);
$updC->execute();


// ===============================
// 8. Registrar validación
// ===============================
$reg = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
    VALUES (?, ?, ?, 'QR')
");
$reg->bind_param("iis", $cup["id"], $cup["comercio_id"], $now);
$reg->execute();


// ===============================
// 9. Actualizar estado si se completó
// ===============================
$nuevasUsadas = $usadas + 1;

if ($nuevasUsadas >= $cup["total_casillas"]) {
    $end = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
    $end->bind_param("i", $cup["id"]);
    $end->execute();

    echo json_encode([
        "status" => "COMPLETADO",
        "mensaje" => "¡Última casilla marcada! 🎉 Cupón completado.",
        "casilla" => $casilla["numero_casilla"]
    ]);
    exit;
}


// ===============================
// 10. Respuesta normal (casilla marcada)
// ===============================
echo json_encode([
    "status" => "OK",
    "mensaje" => "Casilla " . $casilla["numero_casilla"] . " marcada correctamente.",
    "faltan" => $cup["total_casillas"] - $nuevasUsadas,
    "casilla" => $casilla["numero_casilla"]
]);
exit;
