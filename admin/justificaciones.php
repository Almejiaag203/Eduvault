<?php
session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
include '../model/conexion.php';

// Verificar si la conexión se estableció correctamente
if (!isset($conexion)) {
    die("Error: No se pudo establecer la conexión a la base de datos.");
}

// Consulta para obtener solo asistencias de profesores con estado 'Faltó' o 'Justificado' y sus justificaciones, incluyendo aula
$sql = "SELECT ta.id_asistencia, CONCAT(tu.nombre, ' ', tu.apellido) AS usuario, tu.aula, ta.fecha, ta.estado, 
               tj.id_justificacion, tj.descripcion, tj.fecha_justificacion, tj.aprobado
        FROM tabla_asistencia ta
        INNER JOIN tabla_usuario tu ON ta.id_usuario = tu.id_usuario
        LEFT JOIN tabla_justificacion tj ON ta.id_asistencia = tj.id_asistencia
        WHERE ta.estado IN ('Faltó', 'Justificado')
        AND tu.id_rol = 2
        ORDER BY ta.fecha DESC";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $asistencias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Justificaciones de Asistencias de Profesores</h1>

    <!-- Mostrar mensajes de éxito o error si existen -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Asistencias con Estado Faltó o Justificado</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th>Aula</th>
                            <th>Fecha de la Asistencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asistencia['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['aula'] ?? 'No asignada'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['estado']); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#justificacionModal<?php echo $asistencia['id_asistencia']; ?>">
                                        <i class="fas fa-eye"></i> Ver Justificación
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal para Justificación -->
                            <div class="modal fade" id="justificacionModal<?php echo $asistencia['id_asistencia']; ?>" tabindex="-1" role="dialog" aria-labelledby="justificacionModalLabel<?php echo $asistencia['id_asistencia']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="justificacionModalLabel<?php echo $asistencia['id_asistencia']; ?>">
                                                Justificación de <?php echo htmlspecialchars($asistencia['usuario'] . ' (' . $asistencia['fecha'] . ')'); ?>
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if ($asistencia['id_justificacion']): ?>
                                                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($asistencia['descripcion']); ?></p>
                                                <p><strong>Fecha de Justificación:</strong> <?php echo htmlspecialchars($asistencia['fecha_justificacion']); ?></p>
                                                <p><strong>Aprobado:</strong> <?php echo $asistencia['aprobado'] ? 'Sí' : 'No'; ?></p>
                                            <?php else: ?>
                                                <p><strong>Descripción:</strong> Sin justificación registrada.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        },
        "pageLength": 10,
        "order": [[2, "desc"]] // Ordenar por fecha descendente por defecto
    });
});
</script>

<?php
include 'includes/footer.php';
?>