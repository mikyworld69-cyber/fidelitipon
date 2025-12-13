<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/phpqrcode/qr_svg.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Listados
$usuarios = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC");
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

$mensaje = "";

// ==========================
//  CREAR CUPÓN
// ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id     = intval($_POST["comercio_id"]);
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    // Código corto único (8 chars)
    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // 1) Insertamos el cupón SIN QR aún
    $sql = $conn->prepare("
        INSERT INTO cupones (usuario_id, comercio_id, titulo, descripcion, estado, fecha_caducidad, codigo, qr_path)
        VALUES (?, ?, ?, ?, 'activo', ?, ?, NULL)
    ");
    $sql->bind_param("iissss", $usuario_id, $comercio_id, $titulo, $descripcion, $fecha_caducidad, $codigo);

    if ($sql->execute()) {

        $cup_id = $sql->insert_id;

        // 2) Generar QR
        $qrDir = "/var/data/uploads/qrs/";
        if (!is_dir($qrDir)) mkdir($qrDir, 0775, true);

        $qrFile = "qr_" . strtoupper($codigo) . ".svg";
        $qrFullPath = $qrDir . $qrFile;

        // Contenido del QR: URL, ID o código
        $qrContent = $codigo;

        QRcode::svg($qrContent, $qrFullPath, 6);

        // 3) Guardar ruta relativa en BD
        $qrDB = "uploads/qrs/" . $qrFile;

        $up = $conn->prepare("UPDATE cupones SET qr_path = ? WHERE id = ?");
        $up->bind_param("si", $qrDB, $cup_id);
        $up->execute();

        header("Location: cupones.php");
        exit;

    } else {
        $mensaje = "❌ Error al crear el cupón.";
    }
}

include "_header.php";
?>

<h1>Crear Cupón</h1>

<div class="card">

<?php if ($mensaje): ?>
<div class="error" style="background:#e74c3c;padding:10px;color:white;border-radius:10px;margin-bottom:15px;">
    <?= $mensaje ?>
</div>
<?php endif; ?>

<form method="POST">

    <label>Comercio *</label>
    <select name="comercio_id" required>
        <option value="">Seleccionar comercio</option>
        <?php while ($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c["nombre"]) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Usuario (opcional)</label>
    <select name="usuario_id">
        <option value="">Sin asignar</option>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u["nombre"]) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Título *</label>
    <input type="text" name="titulo" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="3"></textarea>

    <label>Fecha de caducidad (opcional)</label>
    <input type="date" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear Cupón</button>

</form>

</div>

<?php include "_footer.php"; ?>
