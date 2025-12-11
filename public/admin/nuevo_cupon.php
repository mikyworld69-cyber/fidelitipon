<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// OBTENER LISTA DE USUARIOS
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");

// OBTENER LISTA DE COMERCIOS
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

$mensaje = "";

// ============================
// GUARDAR CUPÓN + GENERAR CASILLAS
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id     = intval($_POST["comercio_id"]);
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;
    $num_casillas    = intval($_POST["num_casillas"]);

    // Código autogenerado (8 chars)
    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Insertar cupón
    $sql = $conn->prepare("
        INSERT INTO cupones (comercio_id, usuario_id, codigo, titulo, descripcion, fecha_caducidad, total_casillas, casillas_marcadas, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'activo')
    ");

    $sql->bind_param(
        "iissssi",
        $comercio_id,
        $usuario_id,
        $codigo,
        $titulo,
        $descripcion,
        $fecha_caducidad,
        $num_casillas
    );

    if ($sql->execute()) {

        $cup_id = $conn->insert_id; // ID del cupón recién creado

        // ============================
        // GENERAR CASILLAS AUTOMÁTICAMENTE
        // ============================
        $insertCas = $conn->prepare("
            INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada, estado)
            VALUES (?, ?, 0, 'pendiente')
        ");

        for ($i = 1; $i <= $num_casillas; $i++) {
            $insertCas->bind_param("ii", $cup_id, $i);
            $insertCas->execute();
        }

        header("Location: cupones.php");
        exit;

    } else {
        $mensaje = "❌ Error al crear el cupón.";
    }
}

include "_header.php";
?>

<h1>Crear Nuevo Cupón</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="error" style="background:#e74c3c; padding:12px; color:white; border-radius:10px; margin-bottom:15px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<form method="POST">

    <label>Comercio *</label>
    <select name="comercio_id" required>
        <option value="">Seleccionar comercio</option>
        <?php while ($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Usuario (opcional)</label>
    <select name="usuario_id">
        <option value="">Sin asignar</option>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>">
                <?= htmlspecialchars($u['nombre']) ?> (<?= $u['telefono'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Título *</label>
    <input type="text" name="titulo" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4"></textarea>

    <label>Número de casillas *</label>
    <input type="number" name="num_casillas" value="10" min="1" required>

    <label>Fecha de caducidad (opcional)</label>
    <input type="datetime-local" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear Cupón</button>

</form>

</div>

<?php include "_footer.php"; ?>
