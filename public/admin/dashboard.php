<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "_header.php";


// ==================================
// 1) TARJETAS SUPERIORES (NÚMEROS)
// ==================================

$totalUsuarios = $conn->query("SELECT COUNT(*) AS t FROM usuarios")->fetch_assoc()["t"];
$totalComercios = $conn->query("SELECT COUNT(*) AS t FROM comercios")->fetch_assoc()["t"];
$totalCupones = $conn->query("SELECT COUNT(*) AS t FROM cupones")->fetch_assoc()["t"];
$totalValidaciones = $conn->query("SELECT COUNT(*) AS t FROM validaciones")->fetch_assoc()["t"];


// ==================================
// 2) DISTRIBUCIÓN ESTADOS (DONUT)
// ==================================

$estados = $conn->query("
    SELECT estado, COUNT(*) AS total
    FROM cupones
    GROUP BY estado
");

$labelsEstado = [];
$dataEstado = [];

while ($e = $estados->fetch_assoc()) {
    $labelsEstado[] = strtoupper($e["estado"]);
    $dataEstado[] = intval($e["total"]);
}


// ==================================
// 3) CUPONES CREADOS POR MES
// ==================================

$cuponesMes = $conn->query("
    SELECT DATE_FORMAT(fecha_generado, '%Y-%m') AS mes, COUNT(*) AS total
    FROM cupones
    GROUP BY mes
    ORDER BY mes ASC
");

$labelsMes = [];
$dataMes = [];

while ($m = $cuponesMes->fetch_assoc()) {
    $labelsMes[] = $m["mes"];
    $dataMes[] = intval($m["total"]);
}


// ==================================
// 4) VALIDACIONES POR COMERCIO
// ==================================

$valCom = $conn->query("
    SELECT com.nombre AS comercio, COUNT(*) AS total
    FROM validaciones v
    LEFT JOIN comercios com ON com.id = v.comercio_id
    GROUP BY v.comercio_id
    ORDER BY total DESC
");

$labelsCom = [];
$dataCom = [];

while ($v = $valCom->fetch_assoc()) {
    $labelsCom[] = $v["comercio"];
    $dataCom[] = intval($v["total"]);
}


// ==================================
// 5) VALIDACIONES POR MES
// ==================================

$valMes = $conn->query("
    SELECT DATE_FORMAT(fecha_validacion, '%Y-%m') AS mes, COUNT(*) AS total
    FROM validaciones
    GROUP BY mes
    ORDER BY mes ASC
");

$labelsValMes = [];
$dataValMes = [];

while ($vm = $valMes->fetch_assoc()) {
    $labelsValMes[] = $vm["mes"];
    $dataValMes[] = intval($vm["total"]);
}

?>

<h1>Dashboard</h1>

<!-- =============================== -->
<!-- TARJETAS RESUMEN -->
<!-- =============================== -->

<div class="dashboard-cards">

    <div class="stat-card">
        <p>Usuarios</p>
        <h2><?= $totalUsuarios ?></h2>
    </div>

    <div class="stat-card">
        <p>Comercios</p>
        <h2><?= $totalComercios ?></h2>
    </div>

    <div class="stat-card">
        <p>Cupones</p>
        <h2><?= $totalCupones ?></h2>
    </div>

    <div class="stat-card">
        <p>Validaciones</p>
        <h2><?= $totalValidaciones ?></h2>
    </div>

</div>


<!-- =============================== -->
<!-- GRÁFICO 1: ESTADOS DE CUPONES -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Distribución de estados de cupones</h3>
    <canvas id="chartEstados"></canvas>
</div>


<!-- =============================== -->
<!-- GRÁFICO 2: CUPONES POR MES -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Cupones creados por mes</h3>
    <canvas id="chartCuponesMes"></canvas>
</div>


<!-- =============================== -->
<!-- GRÁFICO 3: VALIDACIONES POR COMERCIO -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Validaciones por comercio</h3>
    <canvas id="chartComercios"></canvas>
</div>


<!-- =============================== -->
<!-- GRÁFICO 4: VALIDACIONES POR MES -->
<!-- =============================== -->

<div class="card" style="margin-top:20px;">
    <h3>Validaciones por mes</h3>
    <canvas id="chartValMes"></canvas>
</div>


<!-- =============================== -->
<!-- SCRIPTS CHART.JS -->
<!-- =============================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const labelsEstado = <?= json_encode($labelsEstado) ?>;
const dataEstado = <?= json_encode($dataEstado) ?>;

new Chart(document.getElementById('chartEstados'), {
    type: 'doughnut',
    data: {
        labels: labelsEstado,
        datasets: [{
            data: dataEstado,
            backgroundColor: ['#27ae60','#2980b9','#c0392b','#8e44ad'],
        }]
    }
});


// CUPONES POR MES
new Chart(document.getElementById('chartCuponesMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labelsMes) ?>,
        datasets: [{
            label: 'Cupones creados',
            data: <?= json_encode($dataMes) ?>,
            borderColor: '#3498db',
            borderWidth: 3,
            fill: false,
            tension: 0.3
        }]
    }
});


// VALIDACIONES POR COMERCIO
new Chart(document.getElementById('chartComercios'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsCom) ?>,
        datasets: [{
            label: 'Validaciones',
            data: <?= json_encode($dataCom) ?>,
            backgroundColor: '#9b59b6'
        }]
    }
});


// VALIDACIONES POR MES
new Chart(document.getElementById('chartValMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labelsValMes) ?>,
        datasets: [{
            label: 'Validaciones',
            data: <?= json_encode($dataValMes) ?>,
            borderColor: '#2ecc71',
            borderWidth: 3,
            fill: false,
            tension: 0.3
        }]
    }
});
</script>

<?php include "_footer.php"; ?>
