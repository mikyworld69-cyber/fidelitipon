<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validación de Cupón</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    font-family: 'Arial', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.card {
    width: 90%;
    max-width: 420px;
    background: white;
    border-radius: 20px;
    padding: 35px 25px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.20);
    animation: fadeIn 0.7s ease, scaleIn 0.7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.8); }
    to   { transform: scale(1); }
}

.icon {
    font-size: 80px;
    margin-bottom: 10px;
    animation: pop 0.5s ease;
}

@keyframes pop {
    0%   { transform: scale(0.6); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.title {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.subtitle {
    font-size: 18px;
    color: #555;
    margin-bottom: 20px;
}

.casilla-num {
    font-size: 70px;
    font-weight: bold;
    margin: 15px 0;
}

.btn {
    display: inline-block;
    margin-top: 25px;
    padding: 12px 20px;
    background: #2980b9;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-size: 16px;
}

.btn:hover {
    background: #1f6fa3;
}
</style>

</head>
<body>

<div class="card">

    <!-- LOGO DEL COMERCIO -->
    <?php if (!empty($logo)): ?>
        <img src="<?= $logo ?>" 
             alt="Logo Comercio" 
             style="width:110px; height:110px; object-fit:contain; margin-bottom:20px;">
    <?php endif; ?>

    <?php if ($status === "error"): ?>
        <div class="icon">❌</div>
        <div class="title">Error</div>
        <div class="subtitle"><?= $error ?></div>

    <?php elseif ($status === "caducado"): ?>
        <div class="icon">⛔</div>
        <div class="title">Cupón Caducado</div>
        <div class="subtitle">Este cupón ya no es válido.</div>

    <?php elseif ($status === "completo" || $status === "completado"): ?>
        <div class="icon">🏆</div>
        <div class="title">Cupón Completado</div>
        <div class="subtitle">Ya no quedan casillas disponibles.</div>

        <script>
        setTimeout(() => lanzarConfeti(), 500);
        </script>

    <?php elseif ($status === "ok"): ?>
        <div class="icon">✔️</div>
        <div class="title">Casilla Marcada</div>

        <div class="casilla-num"><?= $casillaMarcada ?></div>

        <div class="subtitle">Faltan <?= $faltan ?> para completar el cupón.</div>

        <script>
        // Vibración
        if (navigator.vibrate) navigator.vibrate(150);

        // Sonido "ding"
        const audio = new Audio("https://cdn.pixabay.com/download/audio/2022/03/15/audio_7df29c9035.mp3?filename=correct-2-46134.mp3");
        audio.play();
        </script>

    <?php endif; ?>

    <a href="/" class="btn">Volver</a>

</div>


<!-- Confeti -->
<script>
function lanzarConfeti() {
    const duration = 2 * 1000;
    const end = Date.now() + duration;

    (function frame() {
        confetti({
            particleCount: 5,
            angle: 60,
            spread: 55,
            origin: { x: 0 }
        });

        confetti({
            particleCount: 5,
            angle: 120,
            spread: 55,
            origin: { x: 1 }
        });

        if (Date.now() < end) requestAnimationFrame(frame);
    })();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

</body>
</html>
