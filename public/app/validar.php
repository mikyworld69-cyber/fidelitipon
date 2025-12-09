<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validar Cupón | Fidelitipon</title>

<!-- Librería QR moderna -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<link rel="stylesheet" href="/public/app/app.css">

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
    padding: 0 0 80px 0;
}

h2 {
    text-align: center;
    padding: 20px;
}

#reader {
    width: 90%;
    max-width: 350px;
    margin: 0 auto;
}

.input {
    width: 80%;
    margin: 20px auto;
    display: block;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #aaa;
}

.btn {
    width: 80%;
    margin: 0 auto;
    display: block;
    padding: 12px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.btn:hover {
    background: #2980b9;
}

.resultado {
    width: 80%;
    margin: 20px auto;
    padding: 15px;
    border-radius: 12px;
    font-size: 18px;
    display: none;
}

.resultado.ok { background: #2ecc71; color: white; }
.resultado.error { background: #c0392b; color: white; }

/* Menú app */
.menu-bottom {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: white;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.menu-bottom a {
    text-decoration: none;
    color: #555;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.menu-bottom a.active {
    color: #3498db;
    font-weight: bold;
}
</style>

</head>
<body>

<h2>📷 Validar Cupón</h2>

<!-- Lector QR -->
<div id="reader"></div>

<!-- Validación manual -->
<input type="text" id="codigo" class="input" placeholder="Introduce código del cupón">

<div class="btn" onclick="validarCodigo()">Validar Manualmente</div>

<div id="resultado" class="resultado"></div>

<!-- Menú inferior -->
<div class="menu-bottom">
    <a href="panel_usuario.php">🏠 Inicio</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php" class="active">📷 Validar</a>
    <a href="../logout.php">🚪 Salir</a>
</div>

<script>
// ⭐ Lector QR
function onScanSuccess(decodedText) {
    validarPeticion(decodedText);
}

const html5QrCode = new Html5Qrcode("reader");
Html5Qrcode.getCameras().then(devices => {
    if (devices.length) {
        html5QrCode.start(
            devices[0].id,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    }
});

// ⭐ Validación manual
function validarCodigo() {
    const codigo = document.getElementById("codigo").value.trim();
    if (codigo === "") return;

    validarPeticion(codigo);
}

// ⭐ Petición AJAX a validar_cupon.php
function validarPeticion(codigo) {

    fetch("../validar_cupon.php", {
        method: "POST",
        body: new URLSearchParams({ codigo })
    })
    .then(res => res.json())
    .then(data => {

        const r = document.getElementById("resultado");
        r.style.display = "block";

        if (data.status === "ok") {
            r.className = "resultado ok";
            r.innerHTML = `
                ✔ Cupón válido<br>
                <strong>${data.titulo}</strong><br>
                ${data.descripcion}<br>
                <br>🎉 ¡Cupón canjeado!
            `;
        } else {
            r.className = "resultado error";
            r.innerHTML = "✘ " + data.msg;
        }
    });
}
</script>

</body>
</html>
