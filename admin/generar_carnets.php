<?php
require_once '../vendor/autoload.php';
include_once 'model/conexion.php';

use Mpdf\Mpdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Configurar zona horaria
date_default_timezone_set('America/Lima');

// Obtener profesores con aula
try {
    $stmt = $conexion->prepare("
        SELECT tu.nombre, tu.apellido, tu.codigo_asistencia, tu.aula
        FROM tabla_usuario tu
        INNER JOIN tabla_rol tr ON tu.id_rol = tr.id_rol
        WHERE tr.nombre = 'Profesor'
        ORDER BY tu.apellido ASC, tu.nombre ASC
    ");
    $stmt->execute();
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener profesores: " . $e->getMessage();
    exit;
}

// Crear PDF
$mpdf = new Mpdf(['format' => 'A4']);

// Estilos CSS mejorados para un diseño más colorido y formateado
$css = '
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
        }
        .carnet {
            width: 350px;
            height: 200px;
            border: 3px solid #007bff;
            border-radius: 15px;
            margin: 30px auto;
            padding: 20px;
            text-align: center;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }
        .carnet::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 50%;
        }
        .carnet h2 {
            font-size: 20px;
            color: #007bff;
            margin: 10px 0 5px 0;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .carnet p {
            font-size: 14px;
            color: #343a40;
            margin: 5px 0;
        }
        .carnet .rol {
            font-size: 16px;
            color: #28a745;
            font-style: italic;
            margin: 5px 0;
        }
        .carnet .aula {
            font-size: 15px;
            color: #fd7e14;
            font-weight: bold;
            margin: 8px 0;
        }
        .carnet .codigo {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
            margin-top: 10px;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .carnet .qr-code {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 90px;
            height: 90px;
            border: 2px solid #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 14px;
            color: #6c757d;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 5px 10px;
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
            font-size: 24px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
    </style>
';

// Generar HTML para los carnets
$html = '<h1>Carnets de Profesores</h1>' . $css;
foreach ($profesores as $profesor) {
    // Generar código QR
    $qrCode = QrCode::create($profesor['codigo_asistencia'])
        ->setSize(90)
        ->setMargin(0);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    $qrBase64 = 'data:image/png;base64,' . base64_encode($result->getString());

    // Manejar aula si es null
    $aula = $profesor['aula'] ?? 'No asignada';

    $html .= '
        <div class="carnet">
            <div class="logo">Sistema de Asistencia</div>
            <img src="' . $qrBase64 . '" class="qr-code" />
            <h2>' . htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido']) . '</h2>
            <p class="rol">Profesor</p>
            <p class="aula">Aula: ' . htmlspecialchars($aula) . '</p>
            <p class="codigo">Código: ' . htmlspecialchars($profesor['codigo_asistencia']) . '</p>
        </div>
    ';
}

// Escribir y descargar PDF
$mpdf->WriteHTML($html);
$mpdf->Output('carnets_profesores_' . date('Ymd_His') . '.pdf', 'D');
exit;
?>