<?php
session_start();
if (!isset($_SESSION['autenticado_profesor']) || $_SESSION['autenticado_profesor'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
include '../model/conexion.php';

// Verificar si la conexión se estableció correctamente
if (!isset($conexion)) {
    die("Error: No se pudo establecer la conexión a la base de datos.");
}

// Obtener datos del formulario
$id_asistencia = $_POST['id_asistencia'] ?? null;
$justificacion = $_POST['justificacion'] ?? null;

if (!$id_asistencia || !$justificacion) {
    header("Location: justificaciones.php?error=Datos incompletos");
    exit();
}

// Verificar que la asistencia pertenece al usuario logueado
$sql_check = "SELECT id_usuario FROM tabla_asistencia WHERE id_asistencia = :id_asistencia";
try {
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':id_asistencia', $id_asistencia, PDO::PARAM_INT);
    $stmt_check->execute();
    $asistencia = $stmt_check->fetch();

    if (!$asistencia || $asistencia['id_usuario'] != $_SESSION['id_usuario']) {
        header("Location: justificaciones.php?error=Asistencia no válida");
        exit();
    }
} catch (PDOException $e) {
    die("Error al verificar la asistencia: " . $e->getMessage());
}

// Iniciar una transacción para asegurar consistencia
try {
    $conexion->beginTransaction();

    // Insertar la justificación en tabla_justificacion
    $sql_insert = "INSERT INTO tabla_justificacion (id_asistencia, descripcion, fecha_justificacion, aprobado)
                   VALUES (:id_asistencia, :descripcion, NOW(), 0)";
    $stmt_insert = $conexion->prepare($sql_insert);
    $stmt_insert->bindParam(':id_asistencia', $id_asistencia, PDO::PARAM_INT);
    $stmt_insert->bindParam(':descripcion', $justificacion, PDO::PARAM_STR);
    $stmt_insert->execute();

    // Actualizar el estado de la asistencia a 'Justificado'
    $sql_update = "UPDATE tabla_asistencia SET estado = 'Justificado' WHERE id_asistencia = :id_asistencia";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bindParam(':id_asistencia', $id_asistencia, PDO::PARAM_INT);
    $stmt_update->execute();

    // Confirmar la transacción
    $conexion->commit();

    header("Location: justificaciones.php?success=Justificación enviada correctamente");
    exit();
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    $conexion->rollBack();
    die("Error al procesar la justificación: " . $e->getMessage());
}
?>