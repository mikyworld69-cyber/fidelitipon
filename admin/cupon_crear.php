<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// =================================================
// CARGAR LISTAS DE USUARIOS Y COMERCIOS
// =================================================
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

// =================================================
// CREACIÓN DE CUPÓN
// =================================================
if (isset($_POST["crear"])) {

    $titulo        = trim($_POST["titulo"]);
    $descripcion   = trim($_POST["descripcion"]);
    $usuario_id    = intval($_POST["usuario_id"]);
    $comercio_id   = intval($_POST["comercio_id"]);
    $fecha_cad     = $_POST["fecha_caducidad"];
    $estado        = $_POST["estado"];

    // Código único de cupón
    $codigo = uniqid("CUP-", true);

    if ($titulo === "" || $descripcion === "" || !$usuario_id || !$comercio_id) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        $ins = $conn->prepare("
            INSERT INTO cupones (titulo, descripcion, usuario_id, comercio_id, fecha_caducidad, estado, codigo)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $ins->bind_param("ssiisss",
            $titulo, 
            $descripcion, 
            $usuario_id, 
            $comercio_id, 
            $fecha_cad,
            $estado,
            $codigo
        );

        $ins->execute();

        header("Location: cupones.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Cupón | Fidelitipon Admin</title>

<link rel="stylesheet" href="admin.css">

<style>
label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
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
    <a href="cupones.php" class="active">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="notificaciones.php">🔔 Notificaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h1>Crear Nuevo Cupón</h1>

    <?php if ($mensaje): ?>
        <div class="card" style="background:#c0392b; color:white;">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <form method="POST">

            <!-- Título -->
            <label>Título</label>
            <input type="text" name="titulo" placeholder="Ej: Descuento 20%" required>

            <!-- Descripción -->
            <label>Descripción</label>
            <textarea name="descripcion" rows="4" placeholder="Describe el cupón..." required></textarea>

            <!-- Usuario -->
            <label>Asignar a Usuario</label>
            <select name="usuario_id" required>
                <option value="">Selecciona un usuario…</option>
                <?php while ($u = $usuarios->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>">
                        <?= htmlspecialchars($u["nombre"] ?: "Sin nombre") ?> — <?= $u["telefono"] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Comercio -->
            <label>Comercio</label>
            <select name="comercio_id" required>
                <option value="">Selecciona un comercio…</option>
                <?php while ($c = $comercios->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c["nombre"]) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Fecha caducidad -->
            <label>Fecha de Caducidad</label>
            <input type="date" name="fecha_caducidad" required>

            <!-- Estado -->
            <label>Estado inicial</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="usado">Usado</option>
                <option value="caducado">Caducado</option>
            </select>

            <br><br>

            <button class="btn btn-success" type="submit" name="crear">
                ✔ Crear Cupón
            </button>

        </form>

    </div>

</div>

</body>
</html>
