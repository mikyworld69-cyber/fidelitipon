<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* ===========================================================
   ============== FILTROS DE REPORTES =========================
   =========================================================== */

$f_comercio = isset($_GET["comercio_id"]) ? intval($_GET["comercio_id"]) : 0;
$f_usuario  = isset($_GET["usuario_id"]) ? intval($_GET["usuario_id"]) : 0;
$f_estado   = isset($_GET["estado"]) ? $_GET["estado"] : "";
$f_fecha    = isset($_GET["fecha"]) ? $_GET["fecha"] : "";

/* ===========================================================
   CARGAR LISTAS PARA FILTROS
=========================================================== */

$comercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");
$usuarios  = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC");

/* ===========================================================
   CONSULTA BASE DE REPORTES
=========================================================== */

$query = "
    SELECT 
        c.id AS cupon_id,
        c.codigo,
        c.titulo,
        c.estado,
        c.fecha_caducidad,
        u.nombre AS usuario_nombre,
        com.nombre AS comercio_nombre,
        (
            SELECT COUNT(*) FROM cupon_casillas 
            WHERE cupon_id = c.id AND marcada = 1
        ) AS casillas_marcadas
    FROM cupones c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    LEFT JOIN comercios com ON com.id = c.comercio_id
    WHERE 1 = 1
";

/* === Aplicar filtros dinámicos === */

if ($f_comercio > 0) {
    $query .= " AND c.comercio_id = $f_comercio ";
}

if ($f_usuario > 0) {
    $query .= " AND c.usuario_id = $f_usuario ";
}

if ($f_estado !== "") {
    $query .= " AND c.estado = '$f_estado' ";
}

if ($f_fecha !== "") {
    $query .= " AND DATE(c.fecha_caducidad) = '$f_fecha' ";
}

$query .= " ORDER BY c.id DESC";

$cupones = $conn->query($query);

/* ===========================================================
   EXPORTACIÓN CSV
=========================================================== */

if (isset($_GET["export"]) && $_GET["export"] === "csv") {

    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=reportes_cupones.csv");

    $out = fopen("php://output", "w");

    fputcsv($out, [
        "ID Cupón", "Código", "Título", "Estado",
        "Usuario", "Comercio", "Casillas Marcadas",
        "Fecha Caducidad"
    ]);

    while ($r = $cupones->fetch_assoc()) {
        fputcsv($out, [
            $r["cupon_id"],
            $r["codigo"],
            $r["titulo"],
            strtoupper($r["estado"]),
            $r["usuario_nombre"],
            $r["comercio_nombre"],
            $r["casillas_marcadas"],
            $r["fecha_caducidad"]
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
<title>Reportes | Fidelitipon Admin</title>
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
    background: #fff;
    padding: 10px;
    border-bottom: 1px solid #eee;
}
.badge {
    padding: 6px 8px;
    border-radius: 8px;
    color: white;
}
.badge-activo { background: #27ae60; }
.badge-usado { background: #7f8c8d; }
.badge-caducado { background: #c0392b; }
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
    <a href="reportes.php" class="active">📈 Reportes</a>
    <a href="logout.php">🚪 Salir</a>
</div>

<!-- CONTENIDO -->
<div class="content">

<h1>Reportes de Cupones</h1>

<div class="card">
<h3>Filtros</h3>

<form method="GET">

    <label>Comercio</label>
    <select name="comercio_id">
        <option value="0">Todos</option>
        <?php while($c = $comercios->fetch_assoc()): ?>
            <option value="<?= $c["id"] ?>" <?= $f_comercio == $c["id"] ? "selected" : "" ?>>
                <?= htmlspecialchars($c["nombre"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Usuario</label>
    <select name="usuario_id">
        <option value="0">Todos</option>
        <?php while($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u["id"] ?>" <?= $f_usuario == $u["id"] ? "selected" : "" ?>>
                <?= htmlspecialchars($u["nombre"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Estado</label>
    <select name="estado">
        <option value="">Todos</option>
        <option value="activo" <?= $f_estado === "activo" ? "selected" : "" ?>>Activo</option>
        <option value="usado" <?= $f_estado === "usado" ? "selected" : "" ?>>Usado</option>
        <option value="caducado" <?= $f_estado === "caducado" ? "selected" : "" ?>>Caducado</option>
    </select>

    <label>Fecha caducidad</label>
    <input type="date" name="fecha" value="<?= $f_fecha ?>">

    <button class="btn btn-success" style="margin-top:10px;">Filtrar</button>

    <a 
        href="reportes.php?<?= http_build_query($_GET) ?>&export=csv" 
        class="btn btn-info" 
        style="margin-left:10px;">
        📥 Exportar CSV
    </a>

</form>
</div>

<div class="card">
<h3>Resultados</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Código</th>
        <th>Título</th>
        <th>Estado</th>
        <th>Usuario</th>
        <th>Comercio</th>
        <th>Casillas</th>
        <th>Caducidad</th>
    </tr>

    <?php while($r = $cupones->fetch_assoc()): 
        $badge = "badge-activo";
        if ($r["estado"] === "usado") $badge = "badge-usado";
        if ($r["estado"] === "caducado") $badge = "badge-caducado";
    ?>
    <tr>
        <td><?= $r["cupon_id"] ?></td>
        <td><?= htmlspecialchars($r["codigo"]) ?></td>
        <td><?= htmlspecialchars($r["titulo"]) ?></td>
        <td><span class="badge <?= $badge ?>"><?= strtoupper($r["estado"]) ?></span></td>
        <td><?= $r["usuario_nombre"] ?></td>
        <td><?= $r["comercio_nombre"] ?></td>
        <td><?= $r["casillas_marcadas"] ?>/10</td>
        <td><?= date("d/m/Y", strtotime($r["fecha_caducidad"])) ?></td>
    </tr>
    <?php endwhile; ?>

</table>

</div>

</div><!-- content -->

</body>
</html>
