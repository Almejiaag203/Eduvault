<?php
include_once '../model/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    try {
        switch ($action) {
            case 'listarRoles':
                $stmt = $conexion->prepare("SELECT id_rol, nombre FROM tabla_rol ORDER BY id_rol ASC");
                $stmt->execute();
                $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'roles' => $roles]);
                break;

            case 'listarUsuarios':
                $stmt = $conexion->prepare("
                    SELECT u.id_usuario, u.nombre, u.apellido, u.usuario, u.codigo_asistencia, r.nombre AS rol, u.id_rol
                    FROM tabla_usuario u
                    LEFT JOIN tabla_rol r ON u.id_rol = r.id_rol
                    ORDER BY u.nombre ASC
                ");
                $stmt->execute();
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'usuarios' => $usuarios]);
                break;

            case 'obtenerUsuario':
                $id = $_POST['id'];
                $stmt = $conexion->prepare("
                    SELECT u.id_usuario, u.nombre, u.apellido, u.usuario, u.codigo_asistencia, r.nombre AS rol, u.id_rol
                    FROM tabla_usuario u
                    LEFT JOIN tabla_rol r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = :id
                ");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'usuario' => $usuario]);
                break;

            case 'registrarUsuario':
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $usuario = $_POST['usuario'];
                $password = md5($_POST['password']);
                $id_rol = $_POST['rol'];

                $stmt = $conexion->prepare("SELECT COUNT(*) FROM tabla_usuario WHERE usuario = :usuario");
                $stmt->bindParam(':usuario', $usuario);
                $stmt->execute();
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
                    exit;
                }

                $stmt = $conexion->prepare("
                    INSERT INTO tabla_usuario (nombre, apellido, usuario, password, id_rol)
                    VALUES (:nombre, :apellido, :usuario, :password, :id_rol)
                ");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':id_rol', $id_rol);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Usuario registrado con éxito']);
                break;

            case 'actualizarUsuario':
                $id = $_POST['id_usuario'];
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $usuario = $_POST['usuario'];
                $id_rol = $_POST['rol'];

                // Verificar si el usuario ya existe (excepto para el usuario actual)
                $stmt = $conexion->prepare("SELECT COUNT(*) FROM tabla_usuario WHERE usuario = :usuario AND id_usuario != :id");
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
                    exit;
                }

                if (!empty($_POST['password'])) {
                    $password = md5($_POST['password']);
                    $stmt = $conexion->prepare("
                        UPDATE tabla_usuario
                        SET nombre = :nombre, apellido = :apellido, usuario = :usuario, password = :password, id_rol = :id_rol
                        WHERE id_usuario = :id
                    ");
                    $stmt->bindParam(':password', $password);
                } else {
                    $stmt = $conexion->prepare("
                        UPDATE tabla_usuario
                        SET nombre = :nombre, apellido = :apellido, usuario = :usuario, id_rol = :id_rol
                        WHERE id_usuario = :id
                    ");
                }
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':id_rol', $id_rol);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado con éxito']);
                break;

            case 'eliminarUsuario':
                $id = $_POST['id'];
                $stmt = $conexion->prepare("DELETE FROM tabla_usuario WHERE id_usuario = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado con éxito']);
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