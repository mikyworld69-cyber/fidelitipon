<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color_msg = "";

// VALIDACIÓN MANUAL
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["codigo_manual"])) {

    $codigo = trim($_POST["codigo_manual"]);

    if ($codigo !== "") {

        // Buscar cupón por código
        $sql = $conn->prepare("
            SELECT id, estado, fecha_caducidad
            FROM cupones
            WHERE codigo = ?
        ");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $cup = $sql->get_result()->fetch_assoc();

        if (!$cup) {
            $mensaje = "❌ No existe ningún cupón con ese código.";
            $color_msg = "#c0392b";
        } else {
            // Llamada directa a la API (uso interno)
            $api = "https://".$_SERVER["HTTP_HOST"]."/admin/api_validar_qr.php?codigo=".$codigo;
            $response = json_decode(file_get_contents($api), true);

            if ($response["status"] === "OK") {
                $mensaje = "✔ Casilla marcada correctamente (Casilla ".$response["casilla"].")";
                $color_msg = "#27ae60";
            }
            elseif ($response["status"] === "COMPLETADO") {
                $mensaje = "🏁 ¡Cupón completado! (Última casilla marcada)";
                $color_msg = "#2980b9";
            }
            elseif ($response["status"] === "CADUCADO") {
                $mensaje = "⚠ Este cupón está CADUCADO.";
                $color_msg = "#c0392b";
            }
            else {
                $mensaje = "❌ ".$response["mensaje"];
                $color_msg = "#c0392b";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validar Cupón | Admin</title>

<link rel="stylesheet" href="admin.css">
<script src="https://unpkg.com/html5-qrcode"></script>

<style>
#qr-reader {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
}
.msg-box {
    padding: 15px;
    border-radius: 10px;
    color: white;
    margin-bottom: 20px;
    text-align: center;
    font-size: 18px;
}
.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
}
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Fidelitipon</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php" class="active">📷 Validar Cupón</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<div class="content">

<h1>Validar Cupón</h1>
<p>Escanea un código QR o introduce el código manualmente:</p>

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

<!-- VALIDACIÓN MANUAL -->
<div class="card">
    <h3>Validación Manual</h3>

    <form method="POST">
        <input type="text" name="codigo_manual" placeholder="Código del cupón" required>
        <button class="btn btn-success">Validar</button>
    </form>
</div>

</div><!-- content -->

<!-- QR SCANNER SCRIPT -->
<script>
function onScanSuccess(decodedText) {

    const url = "/admin/api_validar_qr.php?codigo=" + encodeURIComponent(decodedText);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            let msg = "";
            if (data.status === "OK") {
                msg = "✔ Casilla marcada correctamente (Casilla " + data.casilla + ")";
            }
            else if (data.status === "COMPLETADO") {
                msg = "🏁 ¡Cupón completado!";
            }
            else if (data.status === "CADUCADO") {
                msg = "⚠ Cupón caducado.";
            }
            else {
                msg = "❌ " + data.mensaje;
            }

            alert(msg);
        })
        .catch(err => {
            alert("Error procesando QR: " + err);
        });
}

Html5Qrcode.getCameras().then(devices => {
    if (devices.length > 0) {
        const cam = devices[0].id;
        const scanner = new Html5Qrcode("qr-reader");

        scanner.start(
            cam,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    }
});
</script>

</body>
</html>
