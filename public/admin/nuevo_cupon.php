<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/phpqrcode/qrlib.php';
require_once __DIR__ . '/../../lib/phpqrcode/qr_svg.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener usuarios y comercios
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

$mensaje = "";
$color = "#c0392b";


// ===========================================================
//   CREAR CUPÓN
// ===========================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id      = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id     = intval($_POST["comercio_id"]);
    $titulo          = trim($_POST["titulo"]);
    $descripcion     = trim($_POST["descripcion"]);
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    // Código único del cupón
    $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Insertar cupón con QR vacío temporalmente
    $sql = $conn->prepare("
        INSERT INTO cupones (comercio_id, usuario_id, codigo, titulo, descripcion, fecha_caducidad, estado, qr_path, fecha_generado)
        VALUES (?, ?, ?, ?, ?, ?, 'activo', NULL, NOW())
    ");

    $sql->bind_param("iissss",
        $comercio_id,
        $usuario_id,
        $codigo,
        $titulo,
        $descripcion,
        $fecha_caducidad
    );

    if (!$sql->execute()) {
        $mensaje = "❌ Error al crear el cupón.";
        $color = "#e74c3c";
    } else {

        $cupon_id = $sql->insert_id;

        // ================
        // GENERAR QR SVG
        // ================

        $qr_dir = __DIR__ . "/../../public/uploads/qrs/";

        if (!file_exists($qr_dir)) {
            mkdir($qr_dir, 0775, true);
        }

        $qr_file = $qr_dir . "qr_" . $codigo . ".svg";

        // Contenido del QR
        $qr_text = $codigo;

        QRcode::svg($qr_text, $qr_file, 8, 2);

        $qr_path_db = "uploads/qrs/qr_" . $codigo . ".svg";

        // Guardar en BD
        $upd = $conn->prepare("UPDATE cupones SET qr_path = ? WHERE id = ?");
        $upd->bind_param("si", $qr_path_db, $cupon_id);
        $upd->execute();


        // =====================================================
        //  CREAR LAS 10 CASILLAS
        // =====================================================
        $stmt = $conn->prepare("
            INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada, estado)
            VALUES (?, ?, 0, 'pendiente')
        ");

        for ($i = 1; $i <= 10; $i++) {
            $stmt->bind_param("ii", $cupon_id, $i);
            $stmt->execute();
        }

        // Redirección final
        header("Location: cupones.php");
        exit;
    }
}

include "_header.php";
?>

<h1>Crear Nuevo Cupón</h1>

<?php if ($mensaje): ?>
    <div style="background: <?= $color ?>; color:white; padding:12px; border-radius:8px; margin-bottom:15px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<div class="card">
<form method="POST">

    <label>Comercio *</label>
    <select name="comercio_id" required>
        <option value="">Seleccionar comercio</option>
        <?php while ($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c["id"] ?>"><?= htmlspecialchars($c["nombre"]) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Usuario (opcional)</label>
    <select name="usuario_id">
        <option value="">Sin asignar</option>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u["id"] ?>">
                <?= htmlspecialchars($u["nombre"]) ?> (<?= htmlspecialchars($u["telefono"]) ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Título *</label>
    <input type="text" name="titulo" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4"></textarea>

    <label>Fecha de caducidad (opcional)</label>
    <input type="date" name="fecha_caducidad">

    <button class="btn-success" style="margin-top:15px;">Crear Cupón</button>

</form>
</div>

<?php include "_footer.php"; ?>
