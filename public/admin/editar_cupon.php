<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// =======================================
// VALIDAR ID
// =======================================
if (!isset($_GET["id"])) {
    die("Cupón no especificado.");
}
$cup_id = intval($_GET["id"]);

// =======================================
// OBTENER DATOS DEL CUPÓN
// =======================================
$sql = $conn->prepare("
    SELECT * FROM cupones
    WHERE id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();

if (!$cup) {
    die("Cupón no encontrado.");
}

$mensaje = "";
$success = false;

// =======================================
// ACTUALIZAR CUPÓN
// =======================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo      = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $usuario_id  = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id = intval($_POST["comercio_id"]);
    $fecha_cad   = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;
    $nuevo_total = intval($_POST["num_casillas"]);

    // Actualizar cupón
    $sqlUp = $conn->prepare("
        UPDATE cupones
        SET titulo = ?, descripcion = ?, usuario_id = ?, comercio_id = ?, fecha_caducidad = ?, total_casillas = ?
        WHERE id = ?
    ");

    $sqlUp->bind_param(
        "ssissii",
        $titulo,
        $descripcion,
        $usuario_id,
        $comercio_id,
        $fecha_cad,
        $nuevo_total,
        $cup_id
    );

    if ($sqlUp->execute()) {

        // ============================================
        // SI CAMBIÓ EL NÚMERO DE CASILLAS → REGENERAR
        // ============================================
        if ($nuevo_total != $cup["total_casillas"]) {

            // Borrar casillas existentes
            $conn->query("DELETE FROM cupon_casillas WHERE cupon_id = $cup_id");

            // Insertar nuevas casillas
            $ins = $conn->prepare("
                INSERT INTO cupon_casillas (cupon_id, numero_casilla, marcada, estado)
                VALUES (?, ?, 0, 'pendiente')
            ");

            for ($i = 1; $i <= $nuevo_total; $i++) {
                $ins->bind_param("ii", $cup_id, $i);
                $ins->execute();
            }

            // Resetear casillas marcadas
            $conn->query("UPDATE cupones SET casillas_marcadas = 0 WHERE id = $cup_id");
        }

        $success = true;
        $mensaje = "✔ Cupón actualizado correctamente";

        // Refrescar datos
        header("Location: editar_cupon.php?id=" . $cup_id . "&ok=1");
        exit;

    } else {
        $mensaje = "❌ Error al actualizar el cupón.";
    }
}

// ==============================
// OBTENER LISTA DE USUARIOS
// ==============================
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");

// ==============================
// OBTENER LISTA DE COMERCIOS
// ==============================
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");

include "_header.php";
?>

<h1>Editar Cupón</h1>

<?php if (isset($_GET["ok"])): ?>
    <div class="card" style="background:#2ecc71;color:white;padding:12px;border-radius:12px;">
        ✔ Cupón actualizado correctamente
    </div>
<?php endif; ?>

<div class="card">

<form method="POST">

    <label>Título *</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($cup['titulo']) ?>" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4"><?= htmlspecialchars($cup['descripcion']) ?></textarea>

    <label>Usuario asignado</label>
    <select name="usuario_id">
        <option value="">Sin asignar</option>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>" <?= $cup['usuario_id']==$u['id']?'selected':'' ?>>
                <?= htmlspecialchars($u["nombre"]) ?> (<?= $u["telefono"] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Comercio *</label>
    <select name="comercio_id" required>
        <?php while ($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $cup['comercio_id']==$c['id']?'selected':'' ?>>
                <?= htmlspecialchars($c["nombre"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Fecha caducidad</label>
    <input type="datetime-local" name="fecha_caducidad"
        value="<?= $cup['fecha_caducidad'] ? date('Y-m-d\TH:i', strtotime($cup['fecha_caducidad'])) : '' ?>">

    <label>Número de casillas *</label>
    <input type="number" name="num_casillas" min="1" value="<?= $cup['total_casillas'] ?>" required>

    <button class="btn-success" style="margin-top:15px;">Actualizar Cupón</button>

</form>

</div>

<?php include "_footer.php"; ?>
