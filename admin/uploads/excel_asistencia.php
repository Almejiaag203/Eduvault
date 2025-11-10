<?php
require_once '../../vendor/autoload.php';
include_once '../model/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configurar zona horaria
date_default_timezone_set('America/Lima');
$fecha_generacion = date('d/m/Y H:i:s');

// Obtener filtros desde GET
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;

try {
    // Construir consulta
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

    $stmt = $conexion->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Agregar título con fecha y hora de generación
    $sheet->setCellValue('A1', 'Reporte de Asistencias');
    $sheet->setCellValue('A2', 'Generado el: ' . $fecha_generacion);
    $sheet->mergeCells('A1:G1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A2')->getFont()->setItalic(true);

    // Encabezados
    $sheet->setCellValue('A4', 'ID Asistencia');
    $sheet->setCellValue('B4', 'Profesor');
    $sheet->setCellValue('C4', 'Aula');
    $sheet->setCellValue('D4', 'Fecha');
    $sheet->setCellValue('E4', 'Hora Entrada');
    $sheet->setCellValue('F4', 'Hora Salida');
    $sheet->setCellValue('G4', 'Estado');

    // Datos
    $row = 5;
    foreach ($asistencias as $asistencia) {
        $sheet->setCellValue("A$row", $asistencia['id_asistencia']);
        $sheet->setCellValue("B$row", $asistencia['nombre'] . ' ' . $asistencia['apellido']);
        $sheet->setCellValue("C$row", $asistencia['aula'] ?? 'No asignada');
        $sheet->setCellValue("D$row", $asistencia['fecha']);
        $sheet->setCellValue("E$row", $asistencia['hora_entrada_real'] ?? '-');
        $sheet->setCellValue("F$row", $asistencia['hora_salida_real'] ?? '-');
        $sheet->setCellValue("G$row", $asistencia['estado']);
        $row++;
    }

    // Establecer formato
    $sheet->getStyle('A4:G4')->getFont()->setBold(true);
    $sheet->getStyle('A4:G' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Ajustar el tamaño de las columnas
    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Exportar
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_asistencias_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (PDOException $e) {
    echo "Error al generar el Excel: " . $e->getMessage();
    exit;
}