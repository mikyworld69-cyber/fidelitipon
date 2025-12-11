<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Cupón no especificado.";
    exit;
}

$cup_id = intval($_GET["id"]);


// ================================
// GUARDAR CAMBIOS
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar"])) {

    $titulo      = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $usuario_id  = !empty($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : null;
    $comercio_id = intval($_POST["comercio_id"]);
    $estado      = $_POST["estado"];
    $fecha_caducidad = !empty($_POST["fecha_caducidad"]) ? $_POST["fecha_caducidad"] : null;

    $sql = $conn->prepare("
        UPDATE cupones 
        SET titulo=?, descripcion=?, usuario_id=?, comercio_id=?, estado=?, fecha_caducidad=?
        WHERE id=?
    ");

    $sql->bind_param(
        "ssisssi",
        $titulo,
        $descripcion,
        $usuario_id,
        $comercio_id,
        $estado,
        $fecha_caducidad,
        $cup_id
    );

    $sql->execute();

    echo "<script>alert('Cambios guardados correctamente');</script>";
}


// ================================
// RESET CASILLAS
// ================================
if (isset($_POST["reset_casillas"])) {
    $conn->query("UPDATE cupon_casillas SET estado=0, fecha_marcado=NULL WHERE cupon_id=$cup_id");
    echo "<script>alert('Casillas reiniciadas');</script>";
}


// ================================
// OBTENER CUPÓN
// ================================
$sql = $conn->prepare("
    SELECT c.*, 
           u.nombre AS usuario_nombre, 
           com.nombre AS comercio_nombre,
           com.logo AS comercio_logo
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE c.id = ?
");
$sql->bind_param("i", $cup_id);
$sql->execute();
$cup = $sql->get_result()->fetch_assoc();


// ================================
// OBTENER LISTA DE USUARIOS Y COMERCIOS
// ================================
$usuarios = $conn->query("SELECT id, nombre, telefono FROM usuarios ORDER BY nombre ASC");
$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");


// ================================
// OBTENER CASILLAS
// ================================
$qCas = $conn->prepare("
    SELECT numero_casilla, estado
    FROM cupon_casillas
    WHERE cupon_id = ?
    ORDER BY numero_casilla ASC
");
$qCas->bind_param("i", $cup_id);
$qCas->execute();
$casillas = $qCas->get_result()->fetch_all(MYSQLI_ASSOC);


// progreso
$total = $cup["total_casillas"];
$usadas = 0;
foreach ($casillas as $c) { if ($c["estado"] == 1) $usadas++; }
$porcentaje = round(($usadas / $total) * 100);

// logo comercio
$logo = $cup["comercio_logo"] ?: "/img/default_logo.png";
?>

<style>
.edit-container {
    background: white;
    width: 90%;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.logo-comercio {
    width: 120px;
    height: 120px;
    object-fit: contain;
    display: block;
    margin: 0 auto 15px;
}

.donut {
    width: 140px;
    height: 140px;
    display: block;
    margin: 15px auto;
}

.donut-text {
    fill: #333;
    font-size: 20px;
    font-weight: bold;
}

.casillas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin: 20px 0;
}

.casilla {
    width: 100%;
    padding-top: 100%;
    border-radius: 12px;
    background: #ddd;
    position: relative;
}

.casilla.marcada {
    background: #2ecc71;
}

.casilla span {
    color: white;
    font-weight: bold;
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
}

button, .btn {
    padding: 10px 16px;
    border-radius: 10px;
    border: none;
    margin-top: 8px;
    cursor: pointer;
}

.btn-save { background:#3498db; color:white; }
.btn-reset { background:#e67e22; color:white; }
.btn-back { background:#7f8c8d; color:white; }
</style>


<div class="edit-container">

    <img src="<?= $logo ?>" class="logo-comercio">

    <h2 style="text-align:center;"><?= htmlspecialchars($cup["titulo"]) ?></h2>

    <!-- DONUT -->
    <svg class="donut" viewBox="0 0 36 36">
        <path 
            d="M18 2.0845
               a 15.9155 15.9155 0 0 1 0 31.831
               a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none"
            stroke="#eee"
            stroke-width="3" />

        <path 
            d="M18 2.0845
               a 15.9155 15.9155 0 0 1 0 31.831
               a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none"
            stroke="#3498db"
            stroke-width="3"
            stroke-dasharray="<?= $porcentaje ?>, 100" />

        <text x="18" y="20.35" class="donut-text" text-anchor="middle">
            <?= $porcentaje ?>%
        </text>
    </svg>

    <!-- CASILLAS -->
    <div class="casillas-grid">
        <?php foreach ($casillas as $c): ?>
            <div class="casilla <?= $c["estado"] ? 'marcada' : '' ?>">
                <span><?= $c["numero_casilla"] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Editar Cupón</h3>

    <form method="POST">

        <label>Título *</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($cup["titulo"]) ?>" required>

        <label>Descripción</label>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($cup["descripcion"]) ?></textarea>

        <label>Usuario asignado</label>
        <select name="usuario_id">
            <option value="">Sin usuario</option>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"
                    <?= ($cup["usuario_id"] == $u['id']) ? "selected" : "" ?>>
                    <?= htmlspecialchars($u['nombre']) ?> (<?= $u['telefono'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Comercio</label>
        <select name="comercio_id" required>
            <?php while ($c = $comercios->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"
                    <?= ($cup["comercio_id"] == $c['id']) ? "selected" : "" ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Estado</label>
        <select name="estado">
            <option value="activo"   <?= $cup["estado"] === "activo" ? "selected" : "" ?>>Activo</option>
            <option value="usado"    <?= $cup["estado"] === "usado"  ? "selected" : "" ?>>Usado</option>
            <option value="caducado" <?= $cup["estado"] === "caducado" ? "selected" : "" ?>>Caducado</option>
        </select>

        <label>Fecha de caducidad</label>
        <input type="datetime-local" name="fecha_caducidad" 
               value="<?= $cup["fecha_caducidad"] ? date('Y-m-d\TH:i', strtotime($cup['fecha_caducidad'])) : '' ?>">

        <button type="submit" name="guardar" class="btn-save">Guardar Cambios</button>
    </form>

    <form method="POST">
        <button type="submit" name="reset_casillas" class="btn-reset"
                onclick="return confirm('¿Seguro que quieres reiniciar TODAS las casillas?');">
            Reiniciar Casillas
        </button>
    </form>

    <a href="cupones.php" class="btn-back">Volver</a>

</div>

<?php include "_footer.php"; ?>
