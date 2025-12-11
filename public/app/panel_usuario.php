<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Validar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["usuario_id"];

// Datos del usuario
$sqlUser = $conn->prepare("SELECT nombre, telefono FROM usuarios WHERE id = ?");
$sqlUser->bind_param("i", $user_id);
$sqlUser->execute();
$user = $sqlUser->get_result()->fetch_assoc();

// Cupones del usuario
$sql = $conn->prepare("
    SELECT id, titulo, descripcion, estado, fecha_caducidad, total_casillas
    FROM cupones
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$sql->bind_param("i", $user_id);
$sql->execute();
$cupones = $sql->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Mis Cupones | Fidelitipon</title>

<style>

body {
    background: #f0f2f5;
    margin: 0;
    font-family: 'Roboto', sans-serif;
}

/* CABECERA PREMIUM */
.app-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    padding: 28px 20px;
    color: white;
    text-align: center;
    font-size: 26px;
    font-weight: bold;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
}

/* TARJETA DE USUARIO */
.user-card {
    background: white;
    width: 90%;
    margin: -40px auto 20px;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 7px 18px rgba(0,0,0,0.1);
    text-align: center;
}

.user-avatar {
    width: 70px;
    height: 70px;
    background: #ddd;
    border-radius: 50%;
    margin: 0 auto 10px;
    background-size: cover;
    background-position: center;
}

.user-name {
    font-size: 20px;
    font-weight: bold;
}

.user-phone {
    font-size: 15px;
    color: #666;
}

/* LISTA DE CUPONES */
.cupon-card {
    background: white;
    width: 90%;
    margin: 15px auto;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 5px 16px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.15s ease;
}

.cupon-card:hover {
    transform: scale(1.02);
}

.cupon-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 6px;
}

.cupon-desc {
    font-size: 14px;
    color: #777;
    margin-bottom: 10px;
}

/* BADGES PREMIUM */
.badge {
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: bold;
    color: white;
}

.badge-activo {
    background: #2ecc71;
}

.badge-usado {
    background: #7f8c8d;
}

.badge-caducado {
    background: #e74c3c;
}

/* GRID DE CASILLAS */
.casillas-mini {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 6px;
    margin-top: 14px;
}

.casilla-mini {
    width: 100%;
    padding-top: 100%;
    background: #ddd;
    border-radius: 8px;
    position: relative;
}

.casilla-mini.marcada {
    background: #2ecc71;
}

.casilla-mini span {
    font-size: 12px;
    font-weight: bold;
    color: white;
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
}

/* MENÚ INFERIOR PREMIUM */
.bottom-nav {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-around;
    padding: 12px 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
}

.bottom-nav a {
    text-align: center;
    font-size: 16px;
    text-decoration: none;
    color: #444;
}

.bottom-nav a.active {
    color: #2575fc;
    font-weight: bold;
}

</style>

</head>

<body>

<div class="app-header">Mis Cupones</div>

<!-- TARJETA DE USUARIO -->
<div class="user-card">
    <div class="user-avatar" style="background-image:url('/img/avatar_default.png');"></div>
    <div class="user-name"><?= htmlspecialchars($user["nombre"]) ?></div>
    <div class="user-phone">📱 <?= htmlspecialchars($user["telefono"]) ?></div>
</div>

<h2 style="margin-left: 20px; color:#333;">Tus cupones</h2>

<?php if ($cupones->num_rows === 0): ?>
    <div class="cupon-card" style="text-align:center;">
        <p>No tienes cupones todavía.</p>
    </div>
<?php endif; ?>


<?php while ($c = $cupones->fetch_assoc()): ?>

    <?php
    // obtener casillas del cupón
    $qCasillas = $conn->prepare("
        SELECT numero_casilla, estado
        FROM cupon_casillas
        WHERE cupon_id = ?
        ORDER BY numero_casilla ASC
    ");
    $qCasillas->bind_param("i", $c["id"]);
    $qCasillas->execute();
    $casillas = $qCasillas->get_result()->fetch_all(MYSQLI_ASSOC);

    $estadoClass = "badge-activo";
    if ($c["estado"] === "usado") $estadoClass = "badge-usado";
    if ($c["estado"] === "caducado") $estadoClass = "badge-caducado";
    ?>

    <div class="cupon-card" onclick="location.href='ver_cupon.php?id=<?= $c['id'] ?>'">
        <div class="cupon-title"><?= htmlspecialchars($c["titulo"]) ?></div>
        <div class="cupon-desc"><?= htmlspecialchars($c["descripcion"]) ?></div>

        <span class="badge <?= $estadoClass ?>"><?= strtoupper($c["estado"]) ?></span>

        <div style="margin-top:10px; color:#7f8c8d; font-size:13px;">
            Caduca: <?= date("d/m/Y", strtotime($c["fecha_caducidad"])) ?>
        </div>

        <!-- MINI-GRID DE CASILLAS -->
        <div class="casillas-mini">
            <?php foreach ($casillas as $cas): ?>
                <div class="casilla-mini <?= $cas["estado"] ? 'marcada' : '' ?>">
                    <span><?= $cas["numero_casilla"] ?></span>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

<?php endwhile; ?>


<!-- MENÚ INFERIOR PREMIUM -->
<div class="bottom-nav">
    <a href="/app/panel_usuario.php" class="active">🏠 Inicio</a>
    <a href="/app/perfil.php">👤 Perfil</a>
    <a href="/logout.php">🚪 Salir</a>
</div>

</body>
</html>
