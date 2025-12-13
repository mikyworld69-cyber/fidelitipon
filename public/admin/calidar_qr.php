<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header("Content-Type: application/json");

// =============================
// VALIDAR SESIÓN ADMIN/COMERCIO
// =============================
if (!isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => "ERROR", "msg" => "No autorizado"]);
    exit;
}

$admin_id = $_SESSION["admin_id"];

// =============================
// VALIDAR PARÁMETRO QR
// =============================
if (!isset($_GET["codigo"])) {
    echo json_encode(["status" => "ERROR", "msg" => "Código QR no recibido"]);
    exit;
}

$codigo = trim($_GET["codigo"]);


// =============================
// 1) OBTENER CUPÓN
// =============================
$sql = $conn->prepare("
    SELECT id, usuario_id, comercio_id, estado
    FROM cupones
    WHERE codigo = ?
    LIMIT 1
");
$sql->bind_param("s", $codigo);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    echo json_encode(["status" => "ERROR", "msg" => "Cupón no encontrado"]);
    exit;
}

$cup_id = $cup["id"];

if ($cup["estado"] === "usado") {
    echo json_encode(["status" => "ERROR", "msg" => "Cupón COMPLETADO anteriormente"]);
    exit;
}

if ($cup["estado"] === "caducado") {
    echo json_encode(["status" => "ERROR", "msg" => "Cupón CADUCADO"]);
    exit;
}


// =============================
// 2) BUSCAR CASILLA LIBRE
// =============================
$sql = $conn->prepare("
    SELECT id, numero_casilla 
    FROM cupon_casillas
    WHERE cupon_id = ? AND marcada = 0
    ORDER BY numero_casilla ASC
    LIMIT 1
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$casilla = $sql->get_result()->fetch_assoc();

if (!$casilla) {
    echo json_encode(["status" => "ERROR", "msg" => "El cupón ya está completo"]);
    exit;
}

$casilla_id = $casilla["id"];
$numero = $casilla["numero_casilla"];


// =============================
// 3) MARCAR CASILLA
// =============================
$sql = $conn->prepare("
    UPDATE cupon_casillas
    SET marcada = 1,
        comercio_id = ?,
        fecha_marcada = NOW()
    WHERE id = ?
");
$sql->bind_param("ii", $cup["comercio_id"], $casilla_id);
$sql->execute();


// =============================
// 4) COMPROBAR SI ES LA ÚLTIMA
// =============================
$sql = $conn->prepare("
    SELECT COUNT(*) AS faltan
    FROM cupon_casillas
    WHERE cupon_id = ? AND marcada = 0
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$left = $sql->get_result()->fetch_assoc()["faltan"];

if ($left == 0) {
    // pasar cupón a USADO (completado)
    $conn->query("UPDATE cupones SET estado='usado' WHERE id = $cup_id");
    $estadoFinal = "COMPLETADO";
} else {
    $estadoFinal = "MARCADA $numero/10";
}


// =============================
// 5) REGISTRAR VALIDACIÓN GLOBAL
// =============================
$sql = $conn->prepare("
    INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
    VALUES (?, ?, NOW(), 'QR')
");
$sql->bind_param("ii", $cup_id, $cup["comercio_id"]);
$sql->execute();


// =============================
// 6) RESPUESTA
// =============================
echo json_encode([
    "status" => "OK",
    "msg" => "Casilla $numero marcada correctamente",
    "progreso" => 10 - $left . "/10",
    "estado" => $estadoFinal
]);

exit;
