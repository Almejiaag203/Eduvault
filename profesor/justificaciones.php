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

// Consulta para obtener las faltas del profesor actual
$sql = "SELECT ta.id_asistencia, ta.fecha, tds.nombre AS dia
        FROM tabla_asistencia ta
        LEFT JOIN tabla_horario th ON ta.id_horario = th.id_horario
        LEFT JOIN tabla_dia_semana tds ON th.id_dia = tds.id_dia
        WHERE ta.id_usuario = :id_usuario AND ta.estado = 'Faltó'
        ORDER BY ta.fecha DESC";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $faltas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Justificaciones</h1>

    <!-- DataTable -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registro de Faltas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Día</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faltas as $falta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($falta['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($falta['dia'] ?? 'No disponible'); ?></td>
                                <td>
                                    <?php
                                    // Verificar si ya existe una justificación para esta falta
                                    $sql_justificacion = "SELECT id_justificacion FROM tabla_justificacion WHERE id_asistencia = :id_asistencia";
                                    $stmt_just = $conexion->prepare($sql_justificacion);
                                    $stmt_just->bindParam(':id_asistencia', $falta['id_asistencia'], PDO::PARAM_INT);
                                    $stmt_just->execute();
                                    $justificacion = $stmt_just->fetch();

                                    if ($justificacion) {
                                        echo '<span class="text-success">Justificación enviada</span>';
                                    } else {
                                    ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#justificacionModal<?php echo $falta['id_asistencia']; ?>">
                                            Justificar
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>

                            <!-- Modal para Justificación -->
                            <div class="modal fade" id="justificacionModal<?php echo $falta['id_asistencia']; ?>" tabindex="-1" role="dialog" aria-labelledby="justificacionModalLabel<?php echo $falta['id_asistencia']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="justificacionModalLabel<?php echo $falta['id_asistencia']; ?>">Justificar Falta del <?php echo $falta['fecha']; ?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="procesar_justificacion.php" method="POST">
                                                <input type="hidden" name="id_asistencia" value="<?php echo $falta['id_asistencia']; ?>">
                                                <div class="form-group">
                                                    <label for="justificacion">Justificación</label>
                                                    <textarea class="form-control" id="justificacion" name="justificacion" rows="4" placeholder="Escribe tu justificación aquí..." required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Enviar Justificación</button>
                                            </form>
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