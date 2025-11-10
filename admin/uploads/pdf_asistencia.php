<?php
require_once '../../vendor/autoload.php';
include_once '../model/conexion.php';

use Mpdf\Mpdf;

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

    // Crear PDF
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 20,
        'margin_bottom' => 20,
    ]);
    $html = '
        <h1 style="text-align: center;">Reporte de Asistencias</h1>
        <p style="text-align: center;">Generado el: ' . htmlspecialchars($fecha_generacion) . '</p>
        <table border="1" style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Profesor</th>
                    <th style="padding: 10px;">Aula</th>
                    <th style="padding: 10px;">Fecha</th>
                    <th style="padding: 10px;">Hora Entrada</th>
                    <th style="padding: 10px;">Hora Salida</th>
                    <th style="padding: 10px;">Estado</th>
                </tr>
            </thead>
            <tbody>
    ';
    foreach ($asistencias as $asistencia) {
        $html .= '
            <tr>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['id_asistencia']) . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellido']) . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['aula'] ?? 'No asignada') . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['fecha']) . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['hora_entrada_real'] ?? '-') . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['hora_salida_real'] ?? '-') . '</td>
                <td style="padding: 10px;">' . htmlspecialchars($asistencia['estado']) . '</td>
            </tr>
        ';
    }
    $html .= '
            </tbody>
        </table>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_asistencias_' . date('Ymd_His') . '.pdf', 'D');
    exit;

} catch (PDOException $e) {
    echo "Error al generar el PDF: " . $e->getMessage();
    exit;
}