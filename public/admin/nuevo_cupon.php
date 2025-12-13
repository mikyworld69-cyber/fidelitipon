<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Si no está logueado el admin → fuera
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Cargar librería QR (solo UNA)
require_once __DIR__ . '/../../lib/phpqrcode/qrlib.php';

$mensaje = "";

// Obtener comercios y usuarios
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");

// ===================================================
// CREAR CUPÓN
// ===================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $comercio_id     = intval($_POST["comercio_id"]);
    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    // Código único (8 caracteres)
    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Insertar cupón
    $sql = $conn->prepare("
        INSERT INTO cupones (comercio_id, usuario_id, codigo, titulo, descripcion, fecha_caducidad, estado)
        VALUES (?, ?, ?, ?, ?, ?, 'activo')
    ");

    $sql->bind_param(
        "iissss",
        $comercio_id,
        $usuario_id,
        $codigo,
        $titulo,
        $descripcion,
        $fecha_caducidad
    );

    if (!$sql->execute()) {
        die("Error insertando cupón: " . $conn->error);
    }

    $cupon_id = $conn->insert_id;


    // ===================================================
    // GENERAR QR (SVG)
    // ===================================================
    $qr_texto = "CUPON-" . $cupon_id;

    // Ruta persistente REAL
    $dir_qr = "/var/data/uploads/qrs/";

    if (!is_dir($dir_qr)) {
        mkdir($dir_qr, 0775, true);
    }

    // Nombre archivo
    $qr_filename = "qr_" . strtoupper(bin2hex(random_bytes(3))) . ".svg";
    $ruta_fichero = $dir_qr . $qr_filename;

    // Generar QR en SVG
    QRcode::svg($qr_texto, $ruta_fichero);

    // Ruta pública que se guarda en BD
    $rutaDB = "uploads/qrs/" . $qr_filename;


    // ===================================================
    // Guardar QR en la BD
    // ===================================================
    $up = $conn->prepare("UPDATE cupones SET qr_path = ? WHERE id = ?");
    $up->bind_param("si", $rutaDB, $cupon_id);
    $up->execute();


    // ===================================================
    // Generar casillas (10 casillas por defecto)
    // ===================================================
    for ($i = 1; $i <= 10; $i++) {
        $st = $conn->prepare("INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada, estado) VALUES (?, ?, 0, 'pendiente')");
        $st->bind_param("ii", $cupon_id, $i);
        $st->execute();
    }


    // Redirigir al listado
    header("Location: cupones.php");
    exit;
}

include "_header.php";
?>

<h1>Crear Nuevo Cupón</h1>

<?php if ($mensaje): ?>
    <div class="error"><?= $mensaje ?></div>
<?php endif; ?>

<div class="card">

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
    <textarea name="descripcion" rows="3"></textarea>

    <label>Fecha de caducidad</label>
    <input type="datetime-local" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear cupón</button>

</form>

</div>

<?php include "_footer.php"; ?>
