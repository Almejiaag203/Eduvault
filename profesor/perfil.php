<?php
session_start();
if (!isset($_SESSION['autenticado_profesor']) || $_SESSION['autenticado_profesor'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];

// Handle form submission to update aula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aula'])) {
    include_once '../model/conexion.php';

    try {
        $nuevo_aula = trim($_POST['aula']);
        $stmt = $conexion->prepare("UPDATE tabla_usuario SET aula = :aula WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':aula', $nuevo_aula);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        // Redirect or refresh to show updated data
        header("Location: perfil.php");
        exit();
    } catch (PDOException $e) {
        $error_message = $e->getMessage();
    }
}

include_once '../model/conexion.php';

try {
    $stmt = $conexion->prepare("
        SELECT nombre, apellido, usuario, password, aula, codigo_asistencia, id_rol
        FROM tabla_usuario
        WHERE id_usuario = :id_usuario
    ");
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>

<?php include_once 'includes/header.php'; ?>

<!-- [ Layout content ] Start -->
<div class="layout-content">
    <div class="container-fluid flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-0">Mi Perfil</h4>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if ($usuario): ?>
                    <form method="POST" action="perfil.php" id="perfilForm">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></p>
                                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['usuario']); ?></p>
                                <p><strong>Código de Asistencia:</strong> <?php echo htmlspecialchars($usuario['codigo_asistencia']); ?></p>
                                <p><strong>Rol:</strong> <?php echo ($usuario['id_rol'] == 1 ? 'Administrador' : 'Profesor'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aula"><strong>Aula:</strong></label>
                                    <input type="text" class="form-control" id="aula" name="aula" value="<?php echo htmlspecialchars($usuario['aula'] ?? ''); ?>" placeholder="Ej. Tutoría de 2A" disabled>
                                </div>
                                <button type="button" class="btn btn-primary mt-2" id="editButton">Editar</button>
                                <button type="submit" class="btn btn-success mt-2" id="saveButton" style="display: none;">Guardar Cambios</button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">No se encontraron datos del usuario.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.getElementById('editButton');
    const saveButton = document.getElementById('saveButton');
    const aulaInput = document.getElementById('aula');
    const perfilForm = document.getElementById('perfilForm');

    // Enable editing when Edit button is clicked
    editButton.addEventListener('click', function() {
        aulaInput.removeAttribute('disabled');
        editButton.style.display = 'none';
        saveButton.style.display = 'inline-block';
    });

    // Handle form submission and disable after save
    perfilForm.addEventListener('submit', function(e) {
        saveButton.disabled = true; // Disable save button during submission
    });

    // Re-disable after page reload (if successful)
    window.addEventListener('load', function() {
        aulaInput.setAttribute('disabled', 'disabled');
        saveButton.style.display = 'none';
        editButton.style.display = 'inline-block';
        saveButton.disabled = false; // Re-enable for next edit
    });
});
</script>