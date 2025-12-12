<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../lib/phpqrcode/qr_svg.php';

$mensaje = "";

$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id     = intval($_POST["comercio_id"]);
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

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

    if ($sql->execute()) {

        $cupon_id = $sql->insert_id;

        // ============================
        // GENERAR QR SVG
        // ============================

        $qrDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/qrs/";
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0775, true);
        }

        $qrPath = $qrDir . "qr_" . $codigo . ".svg";
        $qrRelative = "uploads/qrs/qr_" . $codigo . ".svg";

        $contenidoQR = "https://fidelitipon.onrender.com/admin/api_validar_qr.php?codigo=" . $codigo;

        // Generar SVG
        QR_SVG::generate($contenidoQR, $qrPath);

        // Guardar la ruta en DB
        $upd = $conn->prepare("UPDATE cupones SET qr_path = ? WHERE id = ?");
        $upd->bind_param("si", $qrRelative, $cupon_id);
        $upd->execute();

        // ============================
        // CREAR CASILLAS (10)
        // ============================

        for ($i = 1; $i <= 10; $i++) {
            $insCas = $conn->prepare("
                INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada)
                VALUES (?, ?, 0)
            ");
            $insCas->bind_param("ii", $cupon_id, $i);
            $insCas->execute();
        }

        header("Location: cupones.php?created=1");
        exit;

    } else {
        $mensaje = "❌ Error al crear el cupón.";
    }
}

include "_header.php";
?>

<h1>Crear Nuevo Cupón</h1>

<?php if (!empty($mensaje)): ?>
    <div class="card" style="background:#e74c3c;color:white;padding:12px;border-radius:10px;margin-bottom:15px;">
        <?= $mensaje ?>
    </div>
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

    <label>Asignar a un Usuario (opcional)</label>
    <select name="usuario_id">
        <option value="">Sin asignar</option>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>">
                <?= htmlspecialchars($u["nombre"]) ?> (<?= $u["telefono"] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Título *</label>
    <input type="text" name="titulo" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4"></textarea>

    <label>Fecha de caducidad</label>
    <input type="date" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear Cupón</button>
</form>
</div>

<?php include "_footer.php"; ?>
