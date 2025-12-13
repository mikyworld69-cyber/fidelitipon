<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "_header.php";
?>

<h1>Validar Cupón</h1>

<div class="card">

    <h3>Validación con Código QR</h3>

    <div id="qr-reader" style="width:100%;max-width:380px;margin:auto;"></div>

    <div id="qr-result" style="margin-top:15px;font-size:18px;font-weight:bold;"></div>
</div>

<div class="card">
    <h3>Validación Manual</h3>
    <form method="POST" action="api_validar.php" onsubmit="return validarManual();">
        <input type="text" id="codigo_manual" name="codigo" placeholder="Código del cupón" required>
        <button class="btn-success" type="submit">Validar Cupón</button>
    </form>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
function onScanSuccess(decodedText) {
    fetch("api_validar.php?codigo=" + decodedText)
        .then(res => res.json())
        .then(data => {
            let box = document.getElementById("qr-result");

            if (data.status === "OK") {
                box.style.color = "green";
                box.innerHTML = "✔ " + data.mensaje;
            } else {
                box.style.color = "red";
                box.innerHTML = "⚠ " + data.mensaje;
            }
        });
}

function validarManual() {
    const code = document.getElementById("codigo_manual").value.trim();
    if (code === "") return false;
    return true;
}

Html5Qrcode.getCameras().then(devices => {
    if (devices.length > 0) {
        const html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            devices[0].id,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    }
});
</script>

<?php include "_footer.php"; ?>
