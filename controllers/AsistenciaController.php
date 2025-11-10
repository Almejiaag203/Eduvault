<?php
include_once '../model/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    try {
        switch ($action) {
            case 'obtenerEstadoBoton':
                // Obtener horario para el día actual
                $dia_semana = date('N', strtotime('now', strtotime('America/Lima')));
                $stmt = $conexion->prepare("
                    SELECT hora_salida, minuto_salida, salida_am_pm
                    FROM tabla_horario
                    WHERE id_dia = :id_dia
                ");
                $stmt->bindParam(':id_dia', $dia_semana);
                $stmt->execute();
                $horario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$horario) {
                    echo json_encode(['success' => true, 'esHoraSalida' => false]);
                    exit;
                }

                // Calcular hora de salida esperada en formato 24h
                $hora_salida_esperada = sprintf(
                    '%02d:%02d:00',
                    $horario['salida_am_pm'] === 'PM' ? $horario['hora_salida'] + 12 : $horario['hora_salida'],
                    $horario['minuto_salida']
                );

                // Hora actual
                $now = new DateTime('now', new DateTimeZone('America/Lima'));
                $hora_actual = $now->format('H:i:s');

                // Comparar si es hora de salida (solo cambia a salida si la hora actual >= hora de salida)
                $esHoraSalida = strtotime($hora_actual) >= strtotime($hora_salida_esperada);

                echo json_encode(['success' => true, 'esHoraSalida' => $esHoraSalida]);
                break;

            case 'verificarMarcacion':
                $codigo_asistencia = $_POST['codigo_asistencia'];

                // Obtener usuario por código de asistencia
                $stmt = $conexion->prepare("
                    SELECT id_usuario, nombre, apellido
                    FROM tabla_usuario
                    WHERE codigo_asistencia = :codigo
                ");
                $stmt->bindParam(':codigo', $codigo_asistencia);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usuario) {
                    echo json_encode(['success' => false, 'message' => 'Código de asistencia inválido']);
                    exit;
                }

                // Obtener última marcación del día actual
                $fecha_actual = date('Y-m-d', strtotime('now', strtotime('America/Lima')));
                $stmt = $conexion->prepare("
                    SELECT hora_entrada_real, hora_salida_real
                    FROM tabla_asistencia
                    WHERE id_usuario = :id_usuario AND fecha = :fecha
                    ORDER BY fecha_registro DESC
                    LIMIT 1
                ");
                $stmt->bindParam(':id_usuario', $usuario['id_usuario']);
                $stmt->bindParam(':fecha', $fecha_actual);
                $stmt->execute();
                $marcacion = $stmt->fetch(PDO::FETCH_ASSOC);

                $response = [
                    'success' => true,
                    'nombre' => $usuario['nombre'],
                    'apellido' => $usuario['apellido']
                ];

                if ($marcacion && $marcacion['hora_entrada_real'] && !$marcacion['hora_salida_real']) {
                    $response['ultima_marcacion'] = 'Entrada: ' . $marcacion['hora_entrada_real'];
                } elseif ($marcacion && $marcacion['hora_salida_real']) {
                    $response['ultima_marcacion'] = 'Salida: ' . $marcacion['hora_salida_real'];
                } else {
                    $response['ultima_marcacion'] = null;
                }

                echo json_encode($response);
                break;

            case 'marcarEntrada':
                $codigo_asistencia = $_POST['codigo_asistencia'];

                // Obtener usuario
                $stmt = $conexion->prepare("
                    SELECT id_usuario
                    FROM tabla_usuario
                    WHERE codigo_asistencia = :codigo
                ");
                $stmt->bindParam(':codigo', $codigo_asistencia);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usuario) {
                    echo json_encode(['success' => false, 'message' => 'Código de asistencia inválido']);
                    exit;
                }

                // Obtener horario para el día actual
                $dia_semana = date('N', strtotime('now', strtotime('America/Lima')));
                $stmt = $conexion->prepare("
                    SELECT id_horario
                    FROM tabla_horario
                    WHERE id_dia = :id_dia
                ");
                $stmt->bindParam(':id_dia', $dia_semana);
                $stmt->execute();
                $horario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$horario) {
                    echo json_encode(['success' => false, 'message' => 'No hay horario definido para este día']);
                    exit;
                }

                // Obtener hora actual (Perú)
                $now = new DateTime('now', new DateTimeZone('America/Lima'));
                $hora_actual = $now->format('H:i:s');

                // Estado: Asistió
                $estado = 'Asistió';

                // Verificar si ya existe una marcación para el día actual
                $fecha_actual = $now->format('Y-m-d');
                $stmt = $conexion->prepare("
                    SELECT id_asistencia
                    FROM tabla_asistencia
                    WHERE id_usuario = :id_usuario AND fecha = :fecha
                ");
                $stmt->bindParam(':id_usuario', $usuario['id_usuario']);
                $stmt->bindParam(':fecha', $fecha_actual);
                $stmt->execute();
                $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($asistencia) {
                    echo json_encode(['success' => false, 'message' => 'Ya se marcó la entrada para hoy']);
                    exit;
                }

                // Insertar marcación de entrada
                $stmt = $conexion->prepare("
                    INSERT INTO tabla_asistencia (id_usuario, id_horario, fecha, hora_entrada_real, estado)
                    VALUES (:id_usuario, :id_horario, :fecha, :hora_entrada_real, :estado)
                ");
                $stmt->bindParam(':id_usuario', $usuario['id_usuario']);
                $stmt->bindParam(':id_horario', $horario['id_horario']);
                $stmt->bindParam(':fecha', $fecha_actual);
                $stmt->bindParam(':hora_entrada_real', $hora_actual);
                $stmt->bindParam(':estado', $estado);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Entrada registrada con éxito']);
                break;

            case 'marcarSalida':
                $codigo_asistencia = $_POST['codigo_asistencia'];

                // Obtener usuario
                $stmt = $conexion->prepare("
                    SELECT id_usuario
                    FROM tabla_usuario
                    WHERE codigo_asistencia = :codigo
                ");
                $stmt->bindParam(':codigo', $codigo_asistencia);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usuario) {
                    echo json_encode(['success' => false, 'message' => 'Código de asistencia inválido']);
                    exit;
                }

                // Obtener marcación de entrada para el día actual
                $fecha_actual = date('Y-m-d', strtotime('now', strtotime('America/Lima')));
                $stmt = $conexion->prepare("
                    SELECT id_asistencia, hora_entrada_real
                    FROM tabla_asistencia
                    WHERE id_usuario = :id_usuario AND fecha = :fecha AND hora_entrada_real IS NOT NULL AND hora_salida_real IS NULL
                ");
                $stmt->bindParam(':id_usuario', $usuario['id_usuario']);
                $stmt->bindParam(':fecha', $fecha_actual);
                $stmt->execute();
                $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$asistencia) {
                    echo json_encode(['success' => false, 'message' => 'No se encontró una entrada para marcar la salida']);
                    exit;
                }

                // Obtener hora actual
                $now = new DateTime('now', new DateTimeZone('America/Lima'));
                $hora_actual = $now->format('H:i:s');

                // Actualizar marcación de salida
                $stmt = $conexion->prepare("
                    UPDATE tabla_asistencia
                    SET hora_salida_real = :hora_salida_real
                    WHERE id_asistencia = :id_asistencia
                ");
                $stmt->bindParam(':hora_salida_real', $hora_actual);
                $stmt->bindParam(':id_asistencia', $asistencia['id_asistencia']);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Salida registrada con éxito']);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}
?>