<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
include "_header.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    echo "Usuario no especificado.";
    exit;
}

$user_id = intval($_GET["id"]);

// ========================================
// GUARDAR CAMBIOS
// ========================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $fecha_registro = trim($_POST["fecha_registro"]);

    // Si el admin quiere cambiar contraseña
    $nueva_pass = !empty($_POST["password"]) ? password_hash($_POST["password"], PASSWORD_BCRYPT) : null;

    if ($nueva_pass) {
        $sql = $conn->prepare("
            UPDATE usuarios 
            SET nombre=?, telefono=?, fecha_registro=?, password=?
            WHERE id=?
        ");
        $sql->bind_param("ssssi", $nombre, $telefono, $fecha_registro, $nueva_pass, $user_id);
    } else {
        $sql = $conn->prepare("
            UPDATE usuarios 
            SET nombre=?, telefono=?, fecha_registro=?
            WHERE id=?
        ");
        $sql->bind_param("sssi", $nombre, $telefono, $fecha_registro, $user_id);
    }

    $sql->execute();

    echo "<script>alert('Datos del usuario actualizados correctamente');</script>";
}


// ========================================
// OBTENER DATOS DEL USUARIO
// ========================================
$sql = $conn->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
");
$sql->bind_param("i", $user_id);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();

if (!$user) {
    echo "Usuario no encontrado.";
    exit;
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

label {
    display:block;
    font-weight:bold;
    margin-top:10px;
}

input {
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
.btn-back { background:#7f8c8d; color:white; }
</style>

<div class="edit-box">

    <h2>✏️ Editar Usuario</h2>

    <form method="POST">

        <label>Nombre *</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($user["nombre"]) ?>" required>

        <label>Teléfono *</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($user["telefono"]) ?>" required>

        <label>Fecha de registro *</label>
        <input type="datetime-local" name="fecha_registro"
            value="<?= date('Y-m-d\TH:i', strtotime($user["fecha_registro"])) ?>">

        <label>Nueva contraseña (opcional)</label>
        <input type="password" name="password" placeholder="Dejar vacío para no cambiar">

        <button type="submit" class="btn-save">Guardar Cambios</button>
        <a href="ver_usuario.php?id=<?= $user_id ?>" class="btn-back">Volver</a>

    </form>

</div>

<?php include "_footer.php"; ?>
