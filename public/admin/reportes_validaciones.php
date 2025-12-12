<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* ===========================================================
   FILTROS
=========================================================== */

$f_comercio = isset($_GET["comercio_id"]) ? intval($_GET["comercio_id"]) : 0;
$f_fecha    = isset($_GET["fecha"]) ? $_GET["fecha"] : "";
$f_cupon    = isset($_GET["cupon_id"]) ? intval($_GET["cupon_id"]) : 0;

/* ===========================================================
   LISTAS PARA FILTROS
=========================================================== */

$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");
$cupones   = $conn->query("SELECT id, codigo, titulo FROM cupones ORDER BY id DESC");

/* ===========================================================
   CONSULTA PRINCIPAL
=========================================================== */

$query = "
    SELECT 
        v.id AS validacion_id,
        v.cupon_id,
        v.fecha_validacion,
        v.casilla,
        v.metodo,
        
        c.codigo,
        c.titulo,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre

    FROM validaciones v
    LEFT JOIN cupones c ON c.id = v.cupon_id
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE 1 = 1
";

/* FILTROS DINÁMICOS */

if ($f_comercio > 0) {
    $query .= " AND com.id = $f_comercio ";
}

if ($f_fecha !== "") {
    $query .= " AND DATE(v.fecha_validacion) = '$f_fecha' ";
}

if ($f_cupon > 0) {
    $query .= " AND v.cupon_id = $f_cupon ";
}

$query .= " ORDER BY v.fecha_validacion DESC ";

$result = $conn->query($query);

/* ===========================================================
   EXPORTACIÓN CSV
=========================================================== */

if (isset($_GET["export"]) && $_GET["export"] === "csv") {

    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=validaciones_comercio.csv");

    $out = fopen("php://output", "w");

    fputcsv($out, [
        "ID Validación",
        "Cupón",
        "Código Cupón",
        "Casilla Marcada",
        "Usuario",
        "Comercio",
        "Fecha Validación",
        "Método"
    ]);

    while ($v = $result->fetch_assoc()) {
        fputcsv($out, [
            $v["validacion_id"],
            $v["titulo"],
            $v["codigo"],
            $v["casilla"],
            $v["usuario_nombre"],
            $v["comercio_nombre"],
            $v["fecha_validacion"],
            $v["metodo"]
        ]);
    }

    fclose($out);
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📈 Reporte Validaciones | Fidelitipon Admin</title>
<link rel="stylesheet" href="admin.css">

<style>
.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.10);
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #3498db;
    color: white;
    padding: 10px;
}
td {
    padding: 10px;
    background: #fff;
    border-bottom: 1px solid #eee;
}
</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Fidelitipon</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="usuarios.php">👤 Usuarios</a>
    <a href="comercios.php">🏪 Comercios</a>
    <a href="cupones.php">🎟 Cupones</a>
    <a href="validar.php">📷 Validar</a>
    <a href="reportes.php">📈 Reportes</a>
    <a href="reportes_validaciones.php" class="active">🧾 Validaciones</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<div class="content">

<h1>🧾 Reporte de Validaciones por Comercio</h1>

<div class="card">
<h3>Filtros</h3>

<form method="GET">

    <label>Comercio</label>
    <select name="comercio_id">
        <option value="0">Todos</option>
        <?php while ($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c["id"] ?>" <?= $f_comercio == $c["id"] ? "selected" : "" ?>>
                <?= htmlspecialchars($c["nombre"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Cupón</label>
    <select name="cupon_id">
        <option value="0">Todos</option>
        <?php while ($c = $cupones->fetch_assoc()): ?>
            <option value="<?= $c["id"] ?>" <?= $f_cupon == $c["id"] ? "selected" : "" ?>>
                #<?= $c["id"] ?> - <?= htmlspecialchars($c["titulo"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Fecha</label>
    <input type="date" name="fecha" value="<?= $f_fecha ?>">

    <button class="btn btn-success" style="margin-top:10px;">Filtrar</button>

    <a 
        href="reportes_validaciones.php?<?= http_build_query($_GET) ?>&export=csv"
        class="btn btn-info"
        style="margin-left:10px;">
        📥 Exportar CSV
    </a>

</form>
</div>

<div class="card">
<h3>Validaciones registradas</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Cupón</th>
        <th>Usuario</th>
        <th>Comercio</th>
        <th>Casilla</th>
        <th>Fecha</th>
        <th>Método</th>
    </tr>

    <?php while ($v = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $v["validacion_id"] ?></td>
        <td>#<?= $v["cupon_id"] ?> - <?= htmlspecialchars($v["titulo"]) ?> (<?= $v["codigo"] ?>)</td>
        <td><?= htmlspecialchars($v["usuario_nombre"]) ?></td>
        <td><?= htmlspecialchars($v["comercio_nombre"]) ?></td>
        <td><?= $v["casilla"] ?></td>
        <td><?= date("d/m/Y H:i", strtotime($v["fecha_validacion"])) ?></td>
        <td><?= strtoupper($v["metodo"]) ?></td>
    </tr>
    <?php endwhile; ?>

</table>

</div>

</div>

</body>
</html>
