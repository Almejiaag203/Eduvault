<?php
// controllers/MaterialControllerProfesor.php

include_once '../model/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    try {
        session_start();
        $id_usuario = $_SESSION['id_usuario'] ?? null;

        if (!$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        switch ($action) {
            case 'listarMaterialesPorProfesor':
                $stmt = $conexion->prepare("
                    SELECT m.id_material, m.archivo, m.link, m.descripcion, m.fecha
                    FROM tabla_materiales m
                    WHERE m.id_usuario = :id_usuario
                    ORDER BY m.fecha DESC
                ");
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->execute();
                $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($materiales as &$mat) {
                    if ($mat['archivo']) {
                        $mat['archivo_url'] = '../../uploads/materiales/' . $mat['archivo'];
                    }
                }
                echo json_encode(['success' => true, 'materiales' => $materiales]);
                break;

            case 'obtenerMaterial':
                $id_material = $_POST['id_material'] ?? null;
                if (!$id_material) {
                    echo json_encode(['success' => false, 'message' => 'ID de material requerido']);
                    exit;
                }
                $stmt = $conexion->prepare("
                    SELECT m.id_material, m.archivo, m.link, m.descripcion, m.fecha,
                           CONCAT(u.nombre, ' ', u.apellido) as profesor_nombre
                    FROM tabla_materiales m
                    JOIN tabla_usuario u ON m.id_usuario = u.id_usuario
                    WHERE m.id_material = :id_material AND m.id_usuario = :id_usuario
                ");
                $stmt->bindParam(':id_material', $id_material);
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->execute();
                $material = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($material && $material['archivo']) {
                    $material['archivo_url'] = '../../uploads/materiales/' . $material['archivo'];
                }
                echo json_encode(['success' => true, 'material' => $material]);
                break;

            case 'subirMaterial':
                $archivo = $_FILES['archivo']['name'] ?? null;
                $link = $_POST['link'] ?? null;
                $descripcion = $_POST['descripcion'] ?? null;

                if (empty($archivo) && empty($link)) {
                    echo json_encode(['success' => false, 'message' => 'Debe proporcionar un archivo o un enlace']);
                    exit;
                }

                if ($archivo) {
                    $target_dir = '../../uploads/materiales/';
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $target_file = $target_dir . basename($archivo);
                    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $target_file)) {
                        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
                        exit;
                    }
                }

                $stmt = $conexion->prepare("
                    INSERT INTO tabla_materiales (id_usuario, archivo, link, descripcion, fecha)
                    VALUES (:id_usuario, :archivo, :link, :descripcion, NOW())
                ");
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':archivo', $archivo);
                $stmt->bindParam(':link', $link);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Material subido correctamente']);
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