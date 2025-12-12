<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "_header.php";
?>

<h1>Validar Cupón (QR)</h1>

<div class="card">
    <h3>Lector QR</h3>
    <div id="qr-reader" style="width:100%; max-width:400px; margin:auto;"></div>
</div>

<div id="resultado" class="card" style="display:none; margin-top:20px;"></div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
function mostrar(msg, color) {
    const box = document.getElementById("resultado");
    box.style.display = "block";
    box.style.background = color;
    box.style.color = "white";
    box.style.padding = "15px";
    box.style.borderRadius = "10px";
    box.innerHTML = msg;
}

function onScanSuccess(decodedText) {

    fetch("api_validar_qr.php?codigo=" + decodedText)
        .then(r => r.json())
        .then(data => {

            if (data.status === "OK") {
                mostrar("✔ Casilla marcada: " + data.casilla, "#27ae60");
            }
            else if (data.status === "COMPLETADO") {
                mostrar("🎉 Cupón completado (casilla " + data.casilla + ")", "#2980b9");
            }
            else if (data.status === "CADUCADO") {
                mostrar("⚠ Cupón caducado", "#c0392b");
            }
            else {
                mostrar("❌ " + data.mensaje, "#c0392b");
            }
        })
        .catch(err => {
            mostrar("Error: " + err, "#c0392b");
        });
}

Html5Qrcode.getCameras().then(devices => {
    if (devices.length > 0) {
        const qr = new Html5Qrcode("qr-reader");
        qr.start(
            devices[0].id,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    }
});
</script>

<?php include "_footer.php"; ?>
