<?php
session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

require_once '../vendor/autoload.php';
include_once 'model/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

// Configurar zona horaria
date_default_timezone_set('America/Lima');

// Manejar exportaciones
if (isset($_GET['export'])) {
    try {
        // Obtener filtro de rol
        $rol = isset($_GET['rol']) ? $_GET['rol'] : null;

        // Construir consulta
        $query = "
            SELECT 
                tu.id_usuario,
                tu.nombre,
                tu.apellido,
                tu.usuario,
                tr.nombre as rol,
                tu.codigo_asistencia
            FROM tabla_usuario tu
            INNER JOIN tabla_rol tr ON tu.id_rol = tr.id_rol
            WHERE 1=1
        ";
        $params = [];

        if ($rol) {
            $query .= " AND tr.nombre = :rol";
            $params[':rol'] = $rol;
        }

        $query .= " ORDER BY tu.apellido ASC, tu.nombre ASC";

        $stmt = $conexion->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_GET['export'] === 'excel') {
            // Crear hoja de cálculo
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Nombre');
            $sheet->setCellValue('C1', 'Apellido');
            $sheet->setCellValue('D1', 'Usuario');
            $sheet->setCellValue('E1', 'Rol');
            $sheet->setCellValue('F1', 'Código Asistencia');

            // Datos
            $row = 2;
            foreach ($usuarios as $usuario) {
                $sheet->setCellValue("A$row", $usuario['id_usuario']);
                $sheet->setCellValue("B$row", $usuario['nombre']);
                $sheet->setCellValue("C$row", $usuario['apellido']);
                $sheet->setCellValue("D$row", $usuario['usuario']);
                $sheet->setCellValue("E$row", $usuario['rol']);
                $sheet->setCellValue("F$row", $usuario['codigo_asistencia']);
                $row++;
            }

            // Exportar a Excel
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="reporte_usuarios_' . date('Ymd_His') . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        } elseif ($_GET['export'] === 'pdf') {
            // Crear PDF
            $mpdf = new Mpdf(['format' => 'A4']);
            $html = '
                <h1 style="text-align: center;">Reporte de Usuarios</h1>
                <table border="1" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Nombre</th>
                            <th style="padding: 10px;">Apellido</th>
                            <th style="padding: 10px;">Usuario</th>
                            <th style="padding: 10px;">Rol</th>
                            <th style="padding: 10px;">Código Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            foreach ($usuarios as $usuario) {
                $html .= '
                    <tr>
                        <td style="padding: 10px;">' . htmlspecialchars($usuario['id_usuario']) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars($usuario['nombre']) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars($usuario['apellido']) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars($usuario['usuario']) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars(ucfirst($usuario['rol'])) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars($usuario['codigo_asistencia']) . '</td>
                    </tr>
                ';
            }
            $html .= '</tbody></table>';

            $mpdf->WriteHTML($html);
            $mpdf->Output('reporte_usuarios_' . date('Ymd_His') . '.pdf', 'D');
            exit;
        }
    } catch (PDOException $e) {
        echo "Error al generar el reporte: " . $e->getMessage();
        exit;
    }
}

// Obtener lista de usuarios con filtro de rol
$rol = isset($_GET['rol']) ? $_GET['rol'] : null;

$query = "
    SELECT 
        tu.id_usuario,
        tu.nombre,
        tu.apellido,
        tu.usuario,
        tr.nombre as rol,
        tu.codigo_asistencia
    FROM tabla_usuario tu
    INNER JOIN tabla_rol tr ON tu.id_rol = tr.id_rol
    WHERE 1=1
";
$params = [];

if ($rol) {
    $query .= " AND tr.nombre = :rol";
    $params[':rol'] = $rol;
}

$query .= " ORDER BY tu.apellido ASC, tu.nombre ASC";

try {
    $stmt = $conexion->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener usuarios: " . $e->getMessage();
    exit;
}

include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Reporte de Usuarios</h1>

    <!-- Filtros y Botones de Exportación -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mb-2 mr-3">
                    <label for="rol" class="mr-2">Rol:</label>
                    <select class="form-control" id="rol" name="rol">
                        <option value="">Todos</option>
                        <option value="Admin" <?php echo $rol === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Profesor" <?php echo $rol === 'Profesor' ? 'selected' : ''; ?>>Profesor</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="?export=excel<?php echo http_build_query(['rol' => $rol]); ?>" class="btn btn-success mb-2 mr-2">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
                <a href="?export=pdf<?php echo http_build_query(['rol' => $rol]); ?>" class="btn btn-danger mb-2 mr-2">
                    <i class="fas fa-file-pdf"></i> Exportar a PDF
                </a>
                <a href="generar_carnets.php" class="btn btn-info mb-2">
                    <i class="fas fa-id-card"></i> Generar Carnets
                </a>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Código Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                                <td><?php echo htmlspecialchars($usuario['codigo_asistencia']); ?></td>
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
            "order": [[2, "asc"]]
        });
    });
</script>