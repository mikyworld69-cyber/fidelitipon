<?php
require_once __DIR__ . '/../head_universal.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
</head>

<body>

<div class="contenedor-app">

    <h2 class="titulo-app">Recuperar contraseña</h2>

    <?php if (isset($_GET["msg"])): ?>
        <div class="mensaje-ok"><?= htmlspecialchars($_GET["msg"]) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="mensaje-error"><?= htmlspecialchars($_GET["error"]) ?></div>
    <?php endif; ?>

    <form action="procesar_recuperar.php" method="POST">
        <label>Email</label>
        <input type="email" name="email" required placeholder="tu@email.com">

        <button class="btn-primario" type="submit">Enviar enlace</button>
    </form>

    <p style="text-align:center;margin-top:20px;">
        <a href="login.php">← Volver al login</a>
    </p>

</div>

</body>
</html>
