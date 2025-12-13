<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: comercios.php");
    exit;
}

$id = intval($_GET["id"]);

$sql = $conn->prepare("SELECT * FROM comercios WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$com = $sql->get_result()->fetch_assoc();

if (!$com) {
    die("Comercio no encontrado.");
}

$mensaje = "";

// ---------------------------------
// GUARDAR CAMBIOS
// ---------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $logo = $com["logo"];

    // ¿Nuevo logo?
    if (!empty($_FILES["logo"]["name"])) {

        $upload_dir = "/var/data/uploads/comercios/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $newname = "comercio_" . time() . "." . $ext;
        $destino = $upload_dir . $newname;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $destino)) {
            $logo = "uploads/comercios/" . $newname;
        }
    }

    $up = $conn->prepare("
        UPDATE comercios
        SET nombre = ?, telefono = ?, logo = ?
        WHERE id = ?
    ");
    $up->bind_param("sssi", $nombre, $telefono, $logo, $id);

    if ($up->execute()) {
        header("Location: comercios.php");
        exit;
    } else {
        $mensaje = "❌ Error al guardar cambios.";
    }
}

include "_header.php";
?>

<h1>Editar Comercio</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="error" style="background:#c0392b;color:white;padding:10px;border-radius:10px;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Nombre *</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($com["nombre"]) ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($com["telefono"]) ?>">

    <label>Logo actual</label><br>
    <?php if (!empty($com["logo"])): ?>
        <img src="/file.php?type=comercio&file=<?= basename($com["logo"]) ?>"
             width="100" style="border-radius:10px; margin-bottom:10px;">
    <?php else: ?>
        <span style="color:#aaa;">Sin logo</span>
    <?php endif; ?>

    <br><br>

    <label>Subir nuevo logo</label>
    <input type="file" name="logo" accept="image/*">

    <button class="btn-success" style="margin-top:15px;">Guardar Cambios</button>

</form>

</div>

<?php include "_footer.php"; ?>
