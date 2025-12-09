<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color_msg = "";

// ====================================================
// VALIDACIÓN MANUAL POR CÓDIGO
// ====================================================
if (isset($_POST["codigo_manual"])) {

    $codigo = trim($_POST["codigo_manual"]);

    if ($codigo !== "") {

        $sql = $conn->prepare("
            SELECT c.*, 
                u.nombre AS usuario_nombre, u.telefono AS usuario_telefono,
                com.nombre AS comercio_nombre
            FROM cupones c
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            LEFT JOIN comercios com ON com.id = c.comercio_id
            WHERE c.codigo = ?
        ");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $cup = $sql->get_result()->fetch_assoc();

        if (!$cup) {
            $mensaje = "Cupón no encontrado.";
            $color_msg = "#c0392b";
        } else {

            // Si el cupón ya está usado
            if ($cup["estado"] === "usado") {
                $mensaje = "Este cupón YA fue validado anteriormente.";
                $color_msg = "#c0392b";
            } 
            // Si está caducado
            else if ($cup["estado"] === "caducado") {
                $mensaje = "Este cupón está CADUCADO.";
                $color_msg = "#c0392b";
            } 
            // Cupón válido ➜ lo marcamos como usado
            else {

                $up = $conn->prepare("UPDATE cupones SET estado='usado' WHERE id=?");
                $up->bind_param("i", $cup["id"]);
                $up->execute();

                // Registrar validación
                $reg = $conn->prepare("
                    INSERT INTO validaciones (cupon_id, fecha_validacion)
                    VALUES (?, NOW())
                ");
                $reg->bind_param("i", $cup["id"]);
                $reg->execute();

                $mensaje = "Cupón VALIDADO con éxito ✔️";
                $color_msg = "#27ae60";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validar Cupón | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

<style>
#qr-reader {
    width: 100%;
    max-width: 420px;
    margin: auto;
}
.msg-box {
    padding: 15px;
    color: white;
    border-radius: 10px;
    font-size: 18px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

<script src="https://unpkg.com/html5-qrcode"></script>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php" class="active">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Validar Cupón</h1>
    <p>Escanea un código QR o introduce el código manualmente.</p>

    <?php if ($mensaje): ?>
        <div class="msg-box" style="background: <?= $color_msg ?>;">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- LECTOR QR -->
    <div class="card">
        <h3>Lector QR</h3>
        <div id="qr-reader"></div>
    </div>

    <!-- FORM VALIDACIÓN MANUAL -->
    <div class="card">

        <h3>Validación Manual</h3>

        <form method="POST">
            <input type="text" name="codigo_manual" placeholder="Introduce el código del cupón">
            <button class="btn btn-success" type="submit">
                Validar Cupón
            </button>
        </form>

    </div>

</div>

<!-- SCRIPT QR -->
<script>
function onScanSuccess(decodedText) {

    // Enviar código escaneado automáticamente al backend
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "validar.php";

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "codigo_manual";
    input.value = decodedText;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

const html5QrCode = new Html5Qrcode("qr-reader");

Html5Qrcode.getCameras().then(devices => {
    if (devices.length > 0) {
        html5QrCode.start(
            devices[0].id,
            {
                fps: 10,
                qrbox: 250
            },
            onScanSuccess
        );
    }
});
</script>

</body>
</html>
