<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validación de Cupón</title>
<style>
body {
    font-family: Arial, sans-serif;
    text-align: center;
    padding: 40px;
    margin: 0;
}

.box {
    max-width: 400px;
    margin: auto;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    color: white;
}

.ok {
    background: #2ecc71;
}

.error, .caducado {
    background: #e74c3c;
}

.completo, .completado {
    background: #f1c40f;
    color: #333;
    font-weight: bold;
}

h1 {
    margin-bottom: 10px;
    font-size: 28px;
}

p {
    font-size: 18px;
    margin-bottom: 10px;
}

.casilla-num {
    font-size: 50px;
    font-weight: bold;
}

</style>
</head>
<body>

<div class="box <?= $status ?>">

    <?php if ($status == "error"): ?>
        <h1>❌ Error</h1>
        <p><?= $error ?></p>

    <?php elseif ($status == "caducado"): ?>
        <h1>⛔ Cupón Caducado</h1>
        <p>No puede validarse.</p>

    <?php elseif ($status == "completo" || $status == "completado"): ?>
        <h1>🏆 Cupón Completado</h1>
        <p>Ya no quedan casillas disponibles.</p>

    <?php elseif ($status == "ok"): ?>
        <h1>✔️ Casilla Marcada</h1>
        <p class="casilla-num"><?= $casillaMarcada ?></p>
        <p>Faltan <?= $faltan ?> casillas para completar el cupón.</p>

    <?php endif; ?>

</div>

</body>
</html>
