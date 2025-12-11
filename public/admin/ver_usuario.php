<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Validar ID de usuario
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$user_id = intval($_GET["id"]);

// ==============================
// OBTENER DATOS DEL USUARIO
// ==============================
$sql = $conn->prepare("
    SELECT id, nombre, telefono, fecha_registro
    FROM usuarios
    WHERE id = ?
");
$sql->bind_param("i", $user_id);
$sql->execute();
$usuario = $sql->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// ==============================
// OBTENER CUPONES DEL USUARIO
// ==============================
$cupones = $conn->prepare("
    SELECT 
        id, titulo, estado, fecha_caducidad, total_casillas, casillas_marcadas
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$cupones->bind_param("i", $user_id);
$cupones->execute();
$res_cupones = $cupones->get_result();

include "_header.php";
?>

<h1>Usuario: <?= htmlspecialchars($usuario["nombre"]) ?></h1>

<div class="card">
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($usuario["telefono"]) ?></p>
    <p><strong>Registrado el:</strong> <?= date("d/m/Y H:i", strtotime($usuario["fecha_registro"])) ?></p>
</div>

<h2>Cupones del Usuario</h2>

<?php if ($res_cupones->num_rows === 0): ?>
    <div class="card">
        <p>Este usuario no tiene cupones.</p>
    </div>

<?php else: ?>

    <?php while ($cup = $res_cupones->fetch_assoc()): ?>

        <div class="card" style="margin-bottom:25px;">

            <h3><?= htmlspecialchars($cup["titulo"]) ?></h3>

            <p><strong>Estado:</strong> <?= strtoupper($cup["estado"]) ?></p>
            <p><strong>Caduca:</strong> 
                <?= $cup["fecha_caducidad"] 
                    ? date("d/m/Y H:i", strtotime($cup["fecha_caducidad"])) 
                    : "—" ?>
            </p>

            <p><strong>Progreso:</strong> 
                <?= $cup["casillas_marcadas"] ?> / <?= $cup["total_casillas"] ?>
            </p>

            <h4>Casillas</h4>

            <?php
            // Obtener casillas del cupón
            $casillas = $conn->prepare("
                SELECT numero_casilla, marcada, estado
                FROM cupon_casillas
                WHERE cupon_id = ?
                ORDER BY numero_casilla ASC
            ");
            $cid = $cup["id"];
            $casillas->bind_param("i", $cid);
            $casillas->execute();
            $res_casillas = $casillas->get_result();
            ?>

            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-top:10px;">
            <?php while ($c = $res_casillas->fetch_assoc()): ?>
                <div style="
                    padding:10px;
                    text-align:center;
                    border-radius:8px;
                    color:white;
                    background: <?= $c['marcada'] ? '#2ecc71' : '#bdc3c7' ?>;
                ">
                    <?= $c["numero_casilla"] ?>
                </div>
            <?php endwhile; ?>
            </div>

        </div>

    <?php endwhile; ?>

<?php endif; ?>

<?php include "_footer.php"; ?>
