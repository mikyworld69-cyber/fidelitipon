<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Comercio no especificado.";
    exit;
}

$comercio_id = intval($_GET["id"]);

// =======================================================
// OBTENER DATOS DEL COMERCIO
// =======================================================
$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $comercio_id);
$sql->execute();
$comercio = $sql->get_result()->fetch_assoc();

if (!$comercio) {
    echo "Comercio no encontrado.";
    exit;
}

$mensaje = "";

// =======================================================
// GUARDAR CAMBIOS
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);

    // LOGO: si suben uno nuevo, sustituimos
    $logo_actual = $comercio["logo"];
    $logo_final = $logo_actual;

    if (!empty($_FILES["logo"]["name"])) {
        
        $dir = __DIR__ . "/../../uploads/comercios/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $tmp = $_FILES["logo"]["tmp_name"];
        $nombre_archivo = time() . "_" . basename($_FILES["logo"]["name"]);
        $ruta_destino = $dir . $nombre_archivo;

        // mover archivo
        if (move_uploaded_file($tmp, $ruta_destino)) {
            $logo_final = "/uploads/comercios/" . $nombre_archivo;
        }
    }

    // Actualizar comercio
    $upd = $conn->prepare("
        UPDATE comercios
        SET nombre=?, logo=?
        WHERE id=?
    ");
    $upd->bind_param("ssi", $nombre, $logo_final, $comercio_id);
    $upd->execute();

    $mensaje = "Cambios guardados correctamente";

    // Recargar datos actualizados
    $sql->execute();
    $comercio = $sql->get_result()->fetch_assoc();
}

?>

<style>
.edit-box {
    width: 90%;
    background: white;
    margin: 20px auto;
    padding: 25px;
    border-radius: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.logo-preview {
    width: 160px;
    height: 160px;
    object-fit: contain;
    margin-bottom: 15px;
    border-radius: 12px;
    background:#f7f7f7;
    padding:10px;
}

label {
    display:block;
    margin-top:10px;
    font-weight:bold;
    color:#333;
}

input[type='text'], input[type='file'] {
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid #ccc;
    margin-top:5px;
}

button {
    padding: 12px 18px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    margin-top:15px;
}

.btn-save { background:#3498db; color:white; }
.btn-back { background:#7f8c8d; color:white; text-decoration:none; padding:12px 18px; border-radius:10px; }
.alert-success {
    background:#2ecc71;
    color:white;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
    font-weight:bold;
}
</style>

<div class="edit-box">

    <h2>✏️ Editar Comercio</h2>

    <?php if ($mensaje): ?>
        <div class="alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nombre del comercio *</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($comercio["nombre"]) ?>" required>

        <label>Logo actual</label>
        <img src="<?= $comercio["logo"] ?: '/img/default_logo.png' ?>" class="logo-preview">

        <label>Subir nuevo logo (opcional)</label>
        <input type="file" name="logo" accept="image/*">

        <button type="submit" class="btn-save">Guardar Cambios</button>
        <a href="ver_comercio.php?id=<?= $comercio_id ?>" class="btn-back">Volver</a>

    </form>

</div>

<?php include "_footer.php"; ?>
