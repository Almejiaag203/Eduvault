<?php
session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

require_once '../vendor/autoload.php';
include_once 'model/conexion.php';

// Configurar zona horaria
date_default_timezone_set('America/Lima');
$fecha_actual = date('Y-m-d');

// Obtener horario para el día actual para verificar si es después de la hora de salida
$dia_semana = date('N');
$stmt = $conexion->prepare("
    SELECT hora_salida, minuto_salida, salida_am_pm
    FROM tabla_horario
    WHERE id_dia = :id_dia
");
$stmt->bindParam(':id_dia', $dia_semana);
$stmt->execute();
$horario = $stmt->fetch(PDO::FETCH_ASSOC);

$esDespuesSalida = false;
if ($horario) {
    $hora_salida_esperada = sprintf(
        '%02d:%02d:00',
        $horario['salida_am_pm'] === 'PM' ? $horario['hora_salida'] + 12 : $horario['hora_salida'],
        $horario['minuto_salida']
    );
    $now = new DateTime('now', new DateTimeZone('America/Lima'));
    $hora_actual = $now->format('H:i:s');
    $esDespuesSalida = strtotime($hora_actual) > strtotime($hora_salida_esperada);
}

// Asignar estado 'Faltó' solo si es después de la hora de salida
if ($esDespuesSalida) {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO tabla_asistencia (id_usuario, fecha, estado)
            SELECT id_usuario, :fecha_actual, 'Faltó'
            FROM tabla_usuario
            WHERE id_usuario NOT IN (
                SELECT id_usuario
                FROM tabla_asistencia
                WHERE fecha = :fecha_actual
            )
        ");
        $stmt->bindParam(':fecha_actual', $fecha_actual);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error al asignar estado 'Faltó': " . $e->getMessage();
        exit;
    }
}

// Obtener lista de profesores para el filtro
try {
    $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, aula FROM tabla_usuario WHERE id_rol = 2 ORDER BY apellido, nombre");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener usuarios: " . $e->getMessage();
    exit;
}

// Obtener asistencias con filtros, incluyendo aula
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;

$query = "
    SELECT 
        ta.id_asistencia,
        ta.id_usuario,
        tu.nombre,
        tu.apellido,
        tu.aula,
        ta.fecha,
        ta.hora_entrada_real,
        ta.hora_salida_real,
        ta.estado
    FROM tabla_asistencia ta
    INNER JOIN tabla_usuario tu ON ta.id_usuario = tu.id_usuario
    WHERE 1=1
";
$params = [];

if ($fecha_inicio) {
    $query .= " AND ta.fecha >= :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio;
}
if ($fecha_fin) {
    $query .= " AND ta.fecha <= :fecha_fin";
    $params[':fecha_fin'] = $fecha_fin;
}
if ($usuario) {
    $query .= " AND ta.id_usuario = :id_usuario";
    $params[':id_usuario'] = $usuario;
}
if ($estado) {
    $query .= " AND ta.estado = :estado";
    $params[':estado'] = $estado;
}

$query .= " ORDER BY ta.fecha DESC, tu.apellido ASC";

try {
    $stmt = $conexion->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener asistencias: " . $e->getMessage();
    exit;
}

include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Reporte de Asistencias</h1>

    <!-- Filtros y Botones de Exportación -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" class="form-inline mb-3">
                <div class="form-group mb-2 mr-3">
                    <label for="fecha_inicio" class="mr-2">Fecha Inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio ?? ''); ?>">
                </div>
                <div class="form-group mb-2 mr-3">
                    <label for="fecha_fin" class="mr-2">Fecha Fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin ?? ''); ?>">
                </div>
                <div class="form-group mb-2 mr-3">
                    <label for="usuario" class="mr-2">Profesor:</label>
                    <select class="form-control" id="usuario" name="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id_usuario']; ?>" <?php echo $usuario == $u['id_usuario'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido'] . ' (' . ($u['aula'] ?? 'No asignada') . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-2 mr-3">
                    <label for="estado" class="mr-2">Estado:</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="Asistió" <?php echo $estado === 'Asistió' ? 'selected' : ''; ?>>Asistió</option>
                        <option value="Faltó" <?php echo $estado === 'Faltó' ? 'selected' : ''; ?>>Faltó</option>
                        <option value="Justificado" <?php echo $estado === 'Justificado' ? 'selected' : ''; ?>>Justificado</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="uploads/excel_asistencia.php?<?php echo http_build_query(['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'usuario' => $usuario, 'estado' => $estado]); ?>" class="btn btn-success mb-2 mr-2">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
                <a href="uploads/pdf_asistencia.php?<?php echo http_build_query(['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'usuario' => $usuario, 'estado' => $estado]); ?>" class="btn btn-danger mb-2">
                    <i class="fas fa-file-pdf"></i> Exportar a PDF
                </a>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Asistencias</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profesor</th>
                            <th>Aula</th>
                            <th>Fecha</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asistencia['id_asistencia']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['aula'] ?? 'No asignada'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['hora_entrada_real'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['hora_salida_real'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($asistencia['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php include 'includes/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Destruir instancia previa de DataTables si existe
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }

        // Inicializar DataTables
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "order": [[3, "desc"]]
        });
    });
</script>