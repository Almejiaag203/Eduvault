<?php
// controllers/MaterialController.php

include_once '../model/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    try {
        switch ($action) {
            case 'listarProfesores':
                $stmt = $conexion->prepare("
                    SELECT id_usuario, CONCAT(nombre, ' ', apellido) as nombre_completo, aula
                    FROM tabla_usuario 
                    WHERE id_rol = 2 
                    ORDER BY nombre_completo
                ");
                $stmt->execute();
                $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'profesores' => $profesores]);
                break;

            case 'listarMaterialesPorProfesor':
                $id_usuario = $_POST['id_usuario'];
                $stmt = $conexion->prepare("
                    SELECT m.id_material, m.archivo, m.link, m.fecha
                    FROM tabla_materiales m
                    WHERE m.id_usuario = :id_usuario
                    ORDER BY m.fecha DESC
                ");
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->execute();
                $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Agregar ruta relativa para archivo si existe (desde admin/ a root/uploads/materiales/)
                foreach ($materiales as &$mat) {
                    if ($mat['archivo']) {
                        $mat['archivo_url'] = '../uploads/materiales/' . $mat['archivo'];
                    }
                }
                echo json_encode(['success' => true, 'materiales' => $materiales]);
                break;

            case 'obtenerMaterial':
                $id_material = $_POST['id_material'];
                $stmt = $conexion->prepare("
                    SELECT m.id_material, m.archivo, m.link, m.fecha,
                           CONCAT(u.nombre, ' ', u.apellido) as profesor_nombre
                    FROM tabla_materiales m
                    JOIN tabla_usuario u ON m.id_usuario = u.id_usuario
                    WHERE m.id_material = :id_material
                ");
                $stmt->bindParam(':id_material', $id_material);
                $stmt->execute();
                $material = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($material && $material['archivo']) {
                    $material['archivo_url'] = '../uploads/materiales/' . $material['archivo'];
                }
                echo json_encode(['success' => true, 'material' => $material]);
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