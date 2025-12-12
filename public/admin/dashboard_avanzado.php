<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* ===========================================================
   MÉTRICAS PRINCIPALES
=========================================================== */

// Total cupones
$total_cupones = $conn->query("SELECT COUNT(*) AS t FROM cupones")->fetch_assoc()["t"];

// Cupones completados
$cupones_completados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='usado'")->fetch_assoc()["t"];

// Cupones activos
$cupones_activos = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='activo'")->fetch_assoc()["t"];

// Comercios
$total_comercios = $conn->query("SELECT COUNT(*) AS t FROM comercios")->fetch_assoc()["t"];

// Usuarios
$total_usuarios = $conn->query("SELECT COUNT(*) AS t FROM usuarios")->fetch_assoc()["t"];

// Validaciones totales
$total_validaciones = $conn->query("SELECT COUNT(*) AS t FROM validaciones")->fetch_assoc()["t"];

// Validaciones hoy
$validaciones_hoy = $conn->query("SELECT COUNT(*) AS t FROM validaciones WHERE DATE(fecha_validacion)=CURDATE()")->fetch_assoc()["t"];

// Casillas marcadas totales
$casillas_marcadas = $conn->query("SELECT COUNT(*) AS t FROM cupon_casillas WHERE marcada=1")->fetch_assoc()["t"];

/* ===========================================================
   VALIDACIONES POR DÍA (últimos 14 días)
=========================================================== */

$graf_dias = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date("Y-m-d", strtotime("-$i days"));
    $q = $conn->query("SELECT COUNT(*) AS t FROM validaciones WHERE DATE(fecha_validacion)='$day'")
           ->fetch_assoc()["t"];
    $graf_dias[] = ["fecha" => $day, "valor" => $q];
}

// Obtener el máximo para escalar
$max_val = max(array_column($graf_dias, "valor"));
$max_val = $max_val < 5 ? 5 : $max_val;

/* ===========================================================
   RANKING COMERCIOS (TOP 5)
=========================================================== */

$ranking_comercios = $conn->query("
    SELECT com.nombre, COUNT(*) AS total
    FROM validaciones v
    LEFT JOIN cupones c ON c.id = v.cupon_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    GROUP BY com.id
    ORDER BY total DESC
    LIMIT 5
");

/* ===========================================================
   RANKING USUARIOS (TOP 5)
=========================================================== */

$ranking_usuarios = $conn->query("
    SELECT u.nombre, COUNT(*) AS total
    FROM validaciones v
    LEFT JOIN cupones c ON c.id = v.cupon_id
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 5
");

/* ===========================================================
   RANKING CUPONES MÁS USADOS
=========================================================== */

$ranking_cupones = $conn->query("
    SELECT c.titulo, COUNT(*) AS total
    FROM validaciones v
    LEFT JOIN cupones c ON c.id = v.cupon_id
    GROUP BY v.cupon_id
    ORDER BY total DESC
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Avanzado | Fidelitipon</title>
<link rel="stylesheet" href="admin.css">

<style>
.card {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}
.metric {
    font-size: 32px;
    font-weight: bold;
    color: #3498db;
}
.label {
    font-size: 15px;
    color: #555;
}
.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap: 20px;
}
h2 { margin-bottom: 8px; }
ul { padding-left: 18px; }
svg {
    width: 100%;
    height: 150px;
}
</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="dashboard_avanzado.php" class="active">🚀 Dashboard Avanzado</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="reportes_validaciones.php">🧾 Validaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<div class="content">

<h1>🚀 Dashboard Avanzado</h1>

<!-- MÉTRICAS -->
<div class="grid-4">
    <div class="card"><div class="metric"><?= $total_validaciones ?></div><div class="label">Validaciones totales</div></div>
    <div class="card"><div class="metric"><?= $validaciones_hoy ?></div><div class="label">Validaciones hoy</div></div>
    <div class="card"><div class="metric"><?= $casillas_marcadas ?></div><div class="label">Casillas marcadas</div></div>
    <div class="card"><div class="metric"><?= $cupones_completados ?></div><div class="label">Cupones completados</div></div>
</div>

<!-- GRÁFICA VALIDACIONES POR DÍA -->
<div class="card">
<h2>📅 Validaciones últimos 14 días</h2>

<svg viewBox="0 0 300 150">
<?php
$width = 300;
$height = 150;
$bar_width = 300 / count($graf_dias);

foreach ($graf_dias as $i => $d):
    $bar_h = ($d["valor"] / $max_val) * 120;
    $x = $i * $bar_width;
    $y = 140 - $bar_h;
?>
    <rect x="<?= $x ?>" y="<?= $y ?>" width="<?= $bar_width - 2 ?>" height="<?= $bar_h ?>" fill="#3498db"/>
<?php endforeach; ?>
</svg>

</div>

<!-- RANKINGS -->
<div class="grid-4">

    <div class="card">
        <h2>🏪 Comercios más activos</h2>
        <ul>
        <?php while ($r = $ranking_comercios->fetch_assoc()): ?>
            <li><?= htmlspecialchars($r["nombre"]) ?> — <strong><?= $r["total"] ?></strong></li>
        <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h2>👤 Usuarios más activos</h2>
        <ul>
        <?php while ($r = $ranking_usuarios->fetch_assoc()): ?>
            <li><?= htmlspecialchars($r["nombre"]) ?> — <strong><?= $r["total"] ?></strong></li>
        <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h2>🎟 Cupones más utilizados</h2>
        <ul>
        <?php while ($r = $ranking_cupones->fetch_assoc()): ?>
            <li><?= htmlspecialchars($r["titulo"]) ?> — <strong><?= $r["total"] ?></strong></li>
        <?php endwhile; ?>
        </ul>
    </div>

</div>

</div>

</body>
</html>
