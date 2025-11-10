<?php
session_start();
if (!isset($_SESSION['autenticado_profesor']) || $_SESSION['autenticado_profesor'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
include '../model/conexion.php'; // Usando tu archivo conexion.php

// Verificar si la conexión se estableció correctamente
if (!isset($conexion)) {
    die("Error: No se pudo establecer la conexión a la base de datos.");
}

// Obtener el ID del usuario logueado desde la sesión
$id_usuario = $_SESSION['id_usuario'];

// Consulta para obtener las asistencias del profesor actual
$sql = "SELECT ta.fecha, tds.nombre AS dia, 
               CONCAT(LPAD(HOUR(ta.hora_entrada_real), 2, '0'), ':', LPAD(MINUTE(ta.hora_entrada_real), 2, '0')) AS hora_entrada,
               CONCAT(LPAD(HOUR(ta.hora_salida_real), 2, '0'), ':', LPAD(MINUTE(ta.hora_salida_real), 2, '0')) AS hora_salida,
               ta.estado
        FROM tabla_asistencia ta
        LEFT JOIN tabla_horario th ON ta.id_horario = th.id_horario
        LEFT JOIN tabla_dia_semana tds ON th.id_dia = tds.id_dia
        WHERE ta.id_usuario = :id_usuario
        ORDER BY ta.fecha DESC";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $asistencias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Ver Mi Asistencia</h1>

    <!-- DataTable -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registro de Mis Asistencias</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Día</th>
                            <th>Hora de Entrada</th>
                            <th>Hora de Salida</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asistencia['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['dia'] ?? 'No disponible'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['hora_entrada'] ?? 'No registrada'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['hora_salida'] ?? 'No registrada'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JavaScript -->
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        "pageLength": 10,
        "order": [[0, "desc"]] // Ordenar por fecha descendente por defecto
    });
});
</script>

<?php
include 'includes/footer.php';
?>