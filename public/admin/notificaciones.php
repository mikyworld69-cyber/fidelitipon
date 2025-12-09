<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Cargar usuarios
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");

// Cargar comercios
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

// Cargar historial de notificaciones
$historial = $conn->query("
    SELECT id, titulo, mensaje, fecha_envio, total_enviados
    FROM notificaciones
    ORDER BY fecha_envio DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notificaciones | Fidelitipon Admin</title>

<link rel="stylesheet" href="admin.css">

<style>
label { font-weight: bold; display:block; margin-bottom:6px; }
select, input, textarea {
    margin-bottom: 12px;
}
.history-box {
    padding: 10px;
    background: white;
    border-radius: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php" class="active">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Enviar Notificaciones Push</h1>

    <div class="card">
        <form method="POST" action="notificaciones_enviar.php">
            
            <!-- Título -->
            <label for="titulo">Título</label>
            <input type="text" name="titulo" placeholder="Ej: Nueva oferta disponible" required>

            <!-- Mensaje -->
            <label for="mensaje">Mensaje</label>
            <textarea name="mensaje" rows="3" placeholder="Texto de la notificación..." required></textarea>

            <!-- Destino -->
            <label>Enviar a:</label>
            <select name="destino" id="destino" required onchange="mostrarOpciones()">
                <option value="todos">📢 Todos los usuarios</option>
                <option value="usuario">👤 Usuario específico</option>
                <option value="comercio">🏪 Usuarios de un comercio</option>
            </select>

            <!-- Selección usuario -->
            <div id="opcion_usuario" style="display:none;">
                <label>Seleccionar usuario</label>
                <select name="usuario_id">
                    <?php while ($u = $usuarios->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['nombre'] ?: "Sin nombre") ?> — <?= $u['telefono'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Selección comercio -->
            <div id="opcion_comercio" style="display:none;">
                <label>Seleccionar comercio</label>
                <select name="comercio_id">
                    <?php while ($c = $comercios->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <br>

            <!-- Botón enviar -->
            <button class="btn btn-success" type="submit">
                🚀 Enviar Notificación
            </button>

        </form>
    </div>

    <h2 style="margin-top:30px;">Historial de Notificaciones</h2>

    <?php while ($n = $historial->fetch_assoc()): ?>
        <div class="history-box">
            <strong><?= htmlspecialchars($n["titulo"]) ?></strong><br>
            <small><?= date("d/m/Y H:i", strtotime($n["fecha_envio"])) ?></small><br>
            <em><?= htmlspecialchars($n["mensaje"]) ?></em><br>
            Enviados: <strong><?= $n["total_enviados"] ?></strong>
        </div>
    <?php endwhile; ?>

</div>

<script>
// Mostrar/ocultar opciones según destino
function mostrarOpciones() {
    document.getElementById("opcion_usuario").style.display = "none";
    document.getElementById("opcion_comercio").style.display = "none";

    const destino = document.getElementById("destino").value;

    if (destino === "usuario") {
        document.getElementById("opcion_usuario").style.display = "block";
    }
    if (destino === "comercio") {
        document.getElementById("opcion_comercio").style.display = "block";
    }
}
</script>

</body>
</html>
