<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Verificar admin
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener lista de comercios
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

// Obtener lista de usuarios
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");

$mensaje = "";

// -------------------------
// GUARDAR CUPÓN
// -------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id     = intval($_POST["comercio_id"]);
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    // Generar código único
    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Crear registro del cupón
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
        $mensaje = "❌ Error al crear el cupón.";
    } else {

        $cup_id = $conn->insert_id;

        // ---------------------------------
        // GENERAR QR SVG EN DISCO PERSISTENTE
        // ---------------------------------

        $qr_dir = "/var/data/uploads/qrs/";
        if (!is_dir($qr_dir)) mkdir($qr_dir, 0775, true);

        $filename = "qr_" . $codigo . ".svg";
        $qr_path = $qr_dir . $filename;

        // GENERAR SVG QR
        require_once __DIR__ . '/../../lib/phpqrcode/qr_svg.php';
        QRcode::svg($codigo, $qr_path, 6);

        // Guardar ruta relativa para file.php
        $qr_relative = "uploads/qrs/" . $filename;

        // Actualizar BD con ruta QR
        $up = $conn->prepare("UPDATE cupones SET qr_path = ? WHERE id = ?");
        $up->bind_param("si", $qr_relative, $cup_id);
        $up->execute();

        // ---------------------------------
        // CREAR 10 CASILLAS DEL CUPÓN
        // ---------------------------------
        $insCas = $conn->prepare("
            INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada)
            VALUES (?, ?, 0)
        ");

        for ($i = 1; $i <= 10; $i++) {
            $insCas->bind_param("ii", $cup_id, $i);
            $insCas->execute();
        }

        header("Location: cupones.php");
        exit;
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

    <label>Fecha de caducidad (opcional)</label>
    <input type="datetime-local" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear Cupón</button>

</form>

</div>

<?php include "_footer.php"; ?>
