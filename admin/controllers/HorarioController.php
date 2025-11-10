<?php
include_once '../model/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    try {
        switch ($action) {
            case 'listarDias':
                $stmt = $conexion->prepare("SELECT id_dia, nombre FROM tabla_dia_semana ORDER BY id_dia ASC");
                $stmt->execute();
                $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'dias' => $dias]);
                break;

            case 'obtenerHorarios':
                $stmt = $conexion->prepare("
                    SELECT h.id_horario, h.id_dia, h.hora_entrada, h.minuto_entrada, h.entrada_am_pm, 
                           h.hora_salida, h.minuto_salida, h.salida_am_pm
                    FROM tabla_horario h
                ");
                $stmt->execute();
                $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($horarios) {
                    echo json_encode(['success' => true, 'horarios' => $horarios]);
                } else {
                    echo json_encode(['success' => true, 'horarios' => [], 'message' => 'No se encontraron horarios']);
                }
                break;

            case 'guardarHorarios':
                // Eliminar horarios existentes
                $stmt = $conexion->prepare("DELETE FROM tabla_horario");
                $stmt->execute();

                // Insertar nuevos horarios
                for ($id_dia = 1; $id_dia <= 7; $id_dia++) {
                    $hora_entrada = isset($_POST["hora_entrada_$id_dia"]) ? $_POST["hora_entrada_$id_dia"] : null;
                    $minuto_entrada = isset($_POST["minuto_entrada_$id_dia"]) ? $_POST["minuto_entrada_$id_dia"] : null;
                    $entrada_am_pm = isset($_POST["entrada_am_pm_$id_dia"]) ? $_POST["entrada_am_pm_$id_dia"] : null;
                    $hora_salida = isset($_POST["hora_salida_$id_dia"]) ? $_POST["hora_salida_$id_dia"] : null;
                    $minuto_salida = isset($_POST["minuto_salida_$id_dia"]) ? $_POST["minuto_salida_$id_dia"] : null;
                    $salida_am_pm = isset($_POST["salida_am_pm_$id_dia"]) ? $_POST["salida_am_pm_$id_dia"] : null;

                    // Validar que todos los campos estén completos para días laborables (1-5) o todos vacíos para días no laborables (6-7)
                    if ($id_dia <= 5) {
                        if (empty($hora_entrada) || empty($minuto_entrada) || empty($entrada_am_pm) || 
                            empty($hora_salida) || empty($minuto_salida) || empty($salida_am_pm)) {
                            continue; // Saltar días laborables con campos incompletos
                        }
                    } else {
                        if (empty($hora_entrada) && empty($minuto_entrada) && empty($entrada_am_pm) && 
                            empty($hora_salida) && empty($minuto_salida) && empty($salida_am_pm)) {
                            continue; // Saltar días no laborables si todos los campos están vacíos
                        }
                        if (empty($hora_entrada) || empty($minuto_entrada) || empty($entrada_am_pm) || 
                            empty($hora_salida) || empty($minuto_salida) || empty($salida_am_pm)) {
                            echo json_encode(['success' => false, 'message' => 'Todos los campos deben estar completos para el día ' . $id_dia]);
                            exit;
                        }
                    }

                    // Validar rangos y valores AM/PM
                    if ($hora_entrada < 0 || $hora_entrada > 23 || $minuto_entrada < 0 || $minuto_entrada > 59 ||
                        $hora_salida < 0 || $hora_salida > 23 || $minuto_salida < 0 || $minuto_salida > 59 ||
                        !in_array($entrada_am_pm, ['AM', 'PM']) || !in_array($salida_am_pm, ['AM', 'PM'])) {
                        echo json_encode(['success' => false, 'message' => 'Valores de hora, minuto o AM/PM fuera de rango para el día ' . $id_dia]);
                        exit;
                    }

                    $stmt = $conexion->prepare("
                        INSERT INTO tabla_horario (id_dia, hora_entrada, minuto_entrada, entrada_am_pm, hora_salida, minuto_salida, salida_am_pm)
                        VALUES (:id_dia, :hora_entrada, :minuto_entrada, :entrada_am_pm, :hora_salida, :minuto_salida, :salida_am_pm)
                    ");
                    $stmt->bindParam(':id_dia', $id_dia);
                    $stmt->bindParam(':hora_entrada', $hora_entrada);
                    $stmt->bindParam(':minuto_entrada', $minuto_entrada);
                    $stmt->bindParam(':entrada_am_pm', $entrada_am_pm);
                    $stmt->bindParam(':hora_salida', $hora_salida);
                    $stmt->bindParam(':minuto_salida', $minuto_salida);
                    $stmt->bindParam(':salida_am_pm', $salida_am_pm);
                    $stmt->execute();
                }

                echo json_encode(['success' => true, 'message' => 'Horarios guardados con éxito']);
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