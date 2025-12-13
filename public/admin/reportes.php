<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "_header.php";

// =====================================
// RESÚMENES SUPERIORES (métricas)
// =====================================

// Total cupones
$totalCupones = $conn->query("SELECT COUNT(*) AS t FROM cupones")->fetch_assoc()["t"];

// Cupones activos
$cuponesActivos = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='activo'")->fetch_assoc()["t"];

// Cupones usados
$cuponesUsados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='usado'")->fetch_assoc()["t"];

// Cupones caducados
$cuponesCaducados = $conn->query("SELECT COUNT(*) AS t FROM cupones WHERE estado='caducado'")->fetch_assoc()["t"];

// Total validaciones
$totalValidaciones = $conn->query("SELECT COUNT(*) AS t FROM validaciones")->fetch_assoc()["t"];

// Lista comercios para filtros
$listaComercios = $conn->query("SELECT id, nombre FROM comercios ORDER BY nombre ASC");


// =====================================
// CONSTRUCCIÓN DEL WHERE (Filtros)
// =====================================

$where = "1";

if (!empty($_GET["comercio"])) {
    $com = intval($_GET["comercio"]);
    $where .= " AND c.comercio_id = $com";
}

if (!empty($_GET["estado"])) {
    $estado = $conn->real_escape_string($_GET["estado"]);
    $where .= " AND c.estado = '$estado'";
}

if (!empty($_GET["desde"])) {
    $desde = $_GET["desde"];
    $where .= " AND c.fecha_caducidad >= '$desde'";
}

if (!empty($_GET["hasta"])) {
    $hasta = $_GET["hasta"];
    $where .= " AND c.fecha_caducidad <= '$hasta'";
}


// =====================================
// CONSULTA PRINCIPAL DE CUPONES
// =====================================

$sql = "
    SELECT
        c.id,
        c.titulo,
        c.descripcion,
        c.estado,
        c.fecha_caducidad,
        com.nombre AS comercio,
        u.nombre AS usuario
    FROM cupones c
    LEFT JOIN comercios com ON c.comercio_id = com.id
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE $where
    ORDER BY c.id DESC
";

$cupones = $conn->query($sql);


// =====================================
// FUNCIONES AUXILIARES
// =====================================

function fecha_legible($f) {
    if (!$f || $f == "0000-00-00" || $f == "0000-00-00 00:00:00") return "—";
    return date("d/m/Y", strtotime($f));
}

?>

<!-- =============================== -->
<!-- TÍTULO -->
<!-- =============================== -->

<h1>Reportes</h1>

<!-- =============================== -->
<!-- TARJETAS DE MÉTRICAS -->
<!-- =============================== -->

<div class="dashboard-cards">

    <div class="stat-card">
        <p>Total Cupones</p>
        <h2><?= $totalCupones ?></h2>
    </div>

    <div class="stat-card">
        <p>Cupones Activos</p>
        <h2 style="color:#27ae60;"><?= $cuponesActivos ?></h2>
    </div>

    <div class="stat-card">
        <p>Cupones Usados</p>
        <h2 style="color:#2980b9;"><?= $cuponesUsados ?></h2>
    </div>

    <div class="stat-card">
        <p>Cupones Caducados</p>
        <h2 style="color:#c0392b;"><?= $cuponesCaducados ?></h2>
    </div>

    <div class="stat-card">
        <p>Total Validaciones</p>
        <h2 style="color:#8e44ad;"><?= $totalValidaciones ?></h2>
    </div>

</div>


<!-- =============================== -->
<!-- FILTROS -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Filtros</h3>

    <form method="GET">

        <label>Comercio:</label>
        <select name="comercio">
            <option value="">Todos</option>
            <?php foreach ($listaComercios as $c): ?>
                <option value="<?= $c["id"] ?>"
                    <?= (isset($_GET["comercio"]) && $_GET["comercio"] == $c["id"]) ? "selected" : "" ?>>
                    <?= htmlspecialchars($c["nombre"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Estado:</label>
        <select name="estado">
            <option value="">Todos</option>
            <option value="activo"   <?= (isset($_GET["estado"]) && $_GET["estado"]=="activo")   ? "selected" : "" ?>>Activo</option>
            <option value="usado"    <?= (isset($_GET["estado"]) && $_GET["estado"]=="usado")    ? "selected" : "" ?>>Usado</option>
            <option value="caducado" <?= (isset($_GET["estado"]) && $_GET["estado"]=="caducado") ? "selected" : "" ?>>Caducado</option>
        </select>

        <label>Caducidad desde:</label>
        <input type="date" name="desde" value="<?= $_GET["desde"] ?? "" ?>">

        <label>Hasta:</label>
        <input type="date" name="hasta" value="<?= $_GET["hasta"] ?? "" ?>">

        <button class="btn-success" style="margin-top:10px;">Aplicar Filtros</button>

    </form>

    <br>

    <!-- BOTONES EXPORTACIÓN -->
    <a class="btn-success"
       style="padding:10px 15px; display:inline-block;"
       href="exportar_reportes.php?<?= http_build_query($_GET) ?>">
        ⬇ Exportar cupones filtrados (CSV)
    </a>

    <a class="btn"
       style="padding:10px 15px; display:inline-block; margin-left:10px;"
       href="exportar_validaciones.php">
        ⬇ Exportar validaciones (CSV)
    </a>

</div>


<!-- =============================== -->
<!-- TABLA DE RESULTADOS -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Cupones encontrados</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Descripción</th>
            <th>Comercio</th>
            <th>Usuario</th>
            <th>Estado</th>
            <th>Caducidad</th>
            <th>Ver</th>
        </tr>

        <?php while ($c = $cupones->fetch_assoc()): ?>
        <tr>
            <td><?= $c["id"] ?></td>
            <td><?= htmlspecialchars($c["titulo"]) ?></td>
            <td><?= htmlspecialchars($c["descripcion"]) ?></td>
            <td><?= htmlspecialchars($c["comercio"] ?: "—") ?></td>
            <td><?= htmlspecialchars($c["usuario"] ?: "—") ?></td>
            <td><?= strtoupper($c["estado"]) ?></td>
            <td><?= fecha_legible($c["fecha_caducidad"]) ?></td>
            <td><a href="ver_cupon_admin.php?id=<?= $c["id"] ?>">👁 Ver</a></td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include "_footer.php"; ?>
