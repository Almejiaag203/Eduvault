<?php
session_start();
if (!isset($_SESSION['autenticado_profesor']) || $_SESSION['autenticado_profesor'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
include '../model/conexion.php';

// Verificar si la conexión se estableció correctamente
if (!isset($conexion)) {
    die("Error: No se pudo establecer la conexión a la base de datos.");
}

// Obtener el ID del usuario logueado
$id_usuario = $_SESSION['id_usuario'];

// Consulta: Total de asistencias del profesor
try {
    $stmt_attendances = $conexion->prepare("SELECT COUNT(*) AS total FROM tabla_asistencia WHERE id_usuario = :id_usuario AND estado = 'Asistió'");
    $stmt_attendances->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_attendances->execute();
    $total_attendances = $stmt_attendances->fetch()['total'];
} catch (PDOException $e) {
    $total_attendances = 0;
    error_log("Error al contar asistencias: " . $e->getMessage());
}

// Consulta: Total de faltas del profesor
try {
    $stmt_absences = $conexion->prepare("SELECT COUNT(*) AS total FROM tabla_asistencia WHERE id_usuario = :id_usuario AND estado = 'Faltó'");
    $stmt_absences->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_absences->execute();
    $total_absences = $stmt_absences->fetch()['total'];
} catch (PDOException $e) {
    $total_absences = 0;
    error_log("Error al contar faltas: " . $e->getMessage());
}

// Consulta: Justificaciones pendientes del profesor
try {
    $stmt_pending = $conexion->prepare("SELECT COUNT(*) AS total FROM tabla_justificacion tj JOIN tabla_asistencia ta ON tj.id_asistencia = ta.id_asistencia WHERE ta.id_usuario = :id_usuario AND tj.aprobado = 0");
    $stmt_pending->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_pending->execute();
    $total_pending = $stmt_pending->fetch()['total'];
} catch (PDOException $e) {
    $total_pending = 0;
    error_log("Error al contar justificaciones pendientes: " . $e->getMessage());
}

// Consulta: Total de justificaciones enviadas por el profesor
try {
    $stmt_justifications = $conexion->prepare("SELECT COUNT(*) AS total FROM tabla_justificacion tj JOIN tabla_asistencia ta ON tj.id_asistencia = ta.id_asistencia WHERE ta.id_usuario = :id_usuario");
    $stmt_justifications->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_justifications->execute();
    $total_justifications = $stmt_justifications->fetch()['total'];
} catch (PDOException $e) {
    $total_justifications = 0;
    error_log("Error al contar justificaciones: " . $e->getMessage());
}

// Consulta: Datos para el gráfico de área (asistencias por mes del profesor)
try {
    $sql_area = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, 
                        SUM(CASE WHEN estado = 'Asistió' THEN 1 ELSE 0 END) AS asistio,
                        SUM(CASE WHEN estado = 'Faltó' THEN 1 ELSE 0 END) AS falto,
                        SUM(CASE WHEN estado = 'Justificado' THEN 1 ELSE 0 END) AS justificado
                 FROM tabla_asistencia
                 WHERE id_usuario = :id_usuario
                 GROUP BY mes
                 ORDER BY mes DESC
                 LIMIT 12"; // Últimos 12 meses
    $stmt_area = $conexion->prepare($sql_area);
    $stmt_area->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_area->execute();
    $area_data = $stmt_area->fetchAll();
} catch (PDOException $e) {
    $area_data = [];
    error_log("Error al obtener datos de área: " . $e->getMessage());
}

// Consulta: Datos para el gráfico de pastel (distribución de estados del profesor)
try {
    $sql_pie = "SELECT estado, COUNT(*) AS total
                FROM tabla_asistencia
                WHERE id_usuario = :id_usuario
                GROUP BY estado";
    $stmt_pie = $conexion->prepare($sql_pie);
    $stmt_pie->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_pie->execute();
    $pie_data = $stmt_pie->fetchAll();
} catch (PDOException $e) {
    $pie_data = [];
    error_log("Error al obtener datos de pastel: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">Generate Report</a>
    </div>
    <div class="row">
        <!-- Total Asistencias -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Asistencias</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_attendances); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Faltas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Faltas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_absences); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Justificaciones Pendientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Justificaciones Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_pending); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Justificaciones -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Justificaciones Enviadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_justifications); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Area Chart: Asistencias por Mes -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Asistencias Mensual</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pie Chart: Distribución de Estados -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Estados</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Asistió
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Faltó
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Justificado
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Area Chart
var ctxArea = document.getElementById('myAreaChart').getContext('2d');
var myAreaChart = new Chart(ctxArea, {
    type: 'line',
    data: {
        labels: [<?php
            $labels = array_map(function($row) { return $row['mes']; }, array_reverse($area_data));
            echo '"' . implode('","', $labels) . '"';
        ?>],
        datasets: [
            {
                label: 'Asistió',
                data: [<?php
                    $asistio = array_map(function($row) { return $row['asistio']; }, array_reverse($area_data));
                    echo implode(',', $asistio);
                ?>],
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                fill: true
            },
            {
                label: 'Faltó',
                data: [<?php
                    $falto = array_map(function($row) { return $row['falto']; }, array_reverse($area_data));
                    echo implode(',', $falto);
                ?>],
                borderColor: '#e74a3b',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                fill: true
            },
            {
                label: 'Justificado',
                data: [<?php
                    $justificado = array_map(function($row) { return $row['justificado']; }, array_reverse($area_data));
                    echo implode(',', $justificado);
                ?>],
                borderColor: '#f6c23e',
                backgroundColor: 'rgba(246, 194, 62, 0.1)',
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Pie Chart
var ctxPie = document.getElementById('myPieChart').getContext('2d');
var myPieChart = new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: [<?php
            $pie_labels = array_map(function($row) { return $row['estado']; }, $pie_data);
            echo '"' . implode('","', $pie_labels) . '"';
        ?>],
        datasets: [{
            data: [<?php
                $pie_values = array_map(function($row) { return $row['total']; }, $pie_data);
                echo implode(',', $pie_values);
            ?>],
            backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e'],
            borderColor: ['#ffffff', '#ffffff', '#ffffff'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
<script src="../js/enforceFooter.js"></script>

<?php
include 'includes/footer.php';
?>