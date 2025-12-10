<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "_header.php";
?>

<h1>Enviar Notificación Push</h1>

<div class="card">

    <form action="notificaciones_enviar.php" method="POST">

        <label>Título *</label>
        <input type="text" name="titulo" required>

        <label>Mensaje *</label>
        <textarea name="mensaje" rows="4" required></textarea>

        <label>URL al pulsar (opcional)</label>
        <input type="text" name="url" placeholder="https://...">

        <button class="btn-success" style="margin-top:20px;">Enviar Notificación</button>
    </form>

</div>

<?php include "_footer.php"; ?>
