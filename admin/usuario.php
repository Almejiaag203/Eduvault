<?php
session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
?>
<?php include_once 'includes/header.php'; ?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Gestión de Usuarios Administradores</h1>

    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addUserModal">
        <i class="fas fa-user-plus"></i> Agregar Administrador
    </button>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Administradores</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable"  width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Usuario</th>
                            <th>Código Asistencia</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <!-- Los usuarios se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Administrador -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Agregar Administrador</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" novalidate>
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                            <div class="invalid-feedback">El nombre es obligatorio.</div>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                            <div class="invalid-feedback">El apellido es obligatorio.</div>
                        </div>
                        <div class="form-group">
                            <label for="usuario">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                            <div class="invalid-feedback">El usuario es obligatorio y debe ser único.</div>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                        <div class="form-group">
                            <label for="rol">Rol <span class="text-danger">*</span></label>
                            <select class="form-control" id="rol" name="rol" required>
                                <option value="">Seleccionar Rol</option>
                                <!-- Los roles se cargarán dinámicamente -->
                            </select>
                            <div class="invalid-feedback">Por favor, seleccione un rol.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveUserBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Usuario -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">Detalles del Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewUserContent">
                    <!-- Contenido dinámico cargado por AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Administrador</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" novalidate>
                        <input type="hidden" id="edit_id_usuario" name="id_usuario">
                        <div class="form-group">
                            <label for="edit_nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            <div class="invalid-feedback">El nombre es obligatorio.</div>
                        </div>
                        <div class="form-group">
                            <label for="edit_apellido">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            <div class="invalid-feedback">El apellido es obligatorio.</div>
                        </div>
                        <div class="form-group">
                            <label for="edit_usuario">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_usuario" name="usuario" required>
                            <div class="invalid-feedback">El usuario es obligatorio y debe ser único.</div>
                        </div>
                        <div class="form-group">
                            <label for="edit_password">Nueva Contraseña (opcional)</label>
                            <input type="password" class="form-control" id="edit_password" name="password" placeholder="Dejar en blanco para no cambiar" minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                        <div class="form-group">
                            <label for="edit_rol">Rol <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_rol" name="rol" required>
                                <option value="">Seleccionar Rol</option>
                                <!-- Los roles se cargarán dinámicamente -->
                            </select>
                            <div class="invalid-feedback">Por favor, seleccione un rol.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveEditUserBtn">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Usuario -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar al usuario <strong id="deleteUserName"></strong>?</p>
                    <p>Esta acción no se puede deshacer.</p>
                    <input type="hidden" id="delete_id_usuario">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->



<script>
document.addEventListener('DOMContentLoaded', function() {
    const usuariosTableBody = document.getElementById('usuariosTableBody');
    const usuarioForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');

  // Función para inicializar DataTable
    function inicializarDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }
        dataTable = $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            }
        });
    }

    // Cargar roles para los selects
    function cargarRoles() {
        fetch('controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'listarRoles'
            })
        })

        
        
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const rolSelect = document.getElementById('rol');
                const editRolSelect = document.getElementById('edit_rol');
                rolSelect.innerHTML = '<option value="">Seleccionar Rol</option>';
                editRolSelect.innerHTML = '<option value="">Seleccionar Rol</option>';
                data.roles.forEach(rol => {
                    rolSelect.innerHTML += `<option value="${rol.id_rol}">${rol.nombre}</option>`;
                    editRolSelect.innerHTML += `<option value="${rol.id_rol}">${rol.nombre}</option>`;
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar roles:', error);
        });
    }

    // Cargar usuarios
    function loadUsuarios() {
        fetch('controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'listarUsuarios'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                usuariosTableBody.innerHTML = '';
                if (data.usuarios.length === 0) {
                    usuariosTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No hay usuarios registrados.</td></tr>';
                    return;
                }
                data.usuarios.forEach(usuario => {
                    usuariosTableBody.innerHTML += `
                        <tr data-id="${usuario.id_usuario}">
                            <td>${usuario.id_usuario}</td>
                            <td>${usuario.nombre}</td>
                            <td>${usuario.apellido}</td>
                            <td>${usuario.usuario}</td>
                            <td>${usuario.codigo_asistencia}</td>
                            <td>${usuario.rol}</td>
                            <td>
                                <button class="btn btn-info btn-sm view-user" data-id="${usuario.id_usuario}" data-toggle="modal" data-target="#viewUserModal">
                                    <i class="fas fa-eye"></i> 
                                </button>
                                <button class="btn btn-warning btn-sm edit-user" data-id="${usuario.id_usuario}" data-toggle="modal" data-target="#editUserModal">
                                    <i class="fas fa-edit"></i> 
                                </button>
                                <button class="btn btn-danger btn-sm delete-user" data-id="${usuario.id_usuario}" data-name="${usuario.nombre} ${usuario.apellido}" data-toggle="modal" data-target="#deleteUserModal">
                                    <i class="fas fa-trash"></i> 
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar los usuarios: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            });
        });
    }

    // Registrar usuario
    document.getElementById('saveUserBtn').addEventListener('click', function(e) {
        e.preventDefault();
        if (!usuarioForm.checkValidity()) {
            usuarioForm.classList.add('was-validated');
            return;
        }

        const formData = new FormData(usuarioForm);
        formData.append('action', 'registrarUsuario');

        fetch('controllers/UsuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    $('#addUserModal').modal('hide');
                    usuarioForm.reset();
                    usuarioForm.classList.remove('was-validated');
                    loadUsuarios();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al registrar el usuario: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            });
        });
    });

    // Ver usuario
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-user')) {
            e.preventDefault();
            const idUsuario = e.target.closest('.view-user').getAttribute('data-id');
            fetch('controllers/UsuarioController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'obtenerUsuario',
                    id: idUsuario
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const usuario = data.usuario;
                    document.getElementById('viewUserContent').innerHTML = `
                        <p><strong>ID:</strong> ${usuario.id_usuario}</p>
                        <p><strong>Nombre:</strong> ${usuario.nombre}</p>
                        <p><strong>Apellido:</strong> ${usuario.apellido}</p>
                        <p><strong>Usuario:</strong> ${usuario.usuario}</p>
                        <p><strong>Código Asistencia:</strong> ${usuario.codigo_asistencia}</p>
                        <p><strong>Rol:</strong> ${usuario.rol}</p>
                        <p><strong>Contraseña:</strong> [Protegida]</p>
                    `;
                }
            });
        }
    });

    // Editar usuario
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-user')) {
            e.preventDefault();
            const idUsuario = e.target.closest('.edit-user').getAttribute('data-id');
            fetch('controllers/UsuarioController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'obtenerUsuario',
                    id: idUsuario
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const usuario = data.usuario;
                    document.getElementById('edit_id_usuario').value = usuario.id_usuario;
                    document.getElementById('edit_nombre').value = usuario.nombre;
                    document.getElementById('edit_apellido').value = usuario.apellido;
                    document.getElementById('edit_usuario').value = usuario.usuario;
                    document.getElementById('edit_password').value = '';
                    document.getElementById('edit_rol').value = usuario.id_rol;
                }
            });
        }
    });

    // Actualizar usuario
    document.getElementById('saveEditUserBtn').addEventListener('click', function(e) {
        e.preventDefault();
        if (!editUserForm.checkValidity()) {
            editUserForm.classList.add('was-validated');
            return;
        }

        const formData = new FormData(editUserForm);
        formData.append('action', 'actualizarUsuario');

        fetch('controllers/UsuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    $('#editUserModal').modal('hide');
                    editUserForm.reset();
                    editUserForm.classList.remove('was-validated');
                    loadUsuarios();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al actualizar el usuario: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            });
        });
    });

    // Eliminar usuario
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-user')) {
            e.preventDefault();
            const idUsuario = e.target.closest('.delete-user').getAttribute('data-id');
            const nombreUsuario = e.target.closest('.delete-user').getAttribute('data-name');
            document.getElementById('delete_id_usuario').value = idUsuario;
            document.getElementById('deleteUserName').textContent = nombreUsuario;
            $('#deleteUserModal').modal('show');
        }
    });

    document.getElementById('confirmDeleteUserBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const idUsuario = document.getElementById('delete_id_usuario').value;

        fetch('controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'eliminarUsuario',
                id: idUsuario
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Eliminado',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    $('#deleteUserModal').modal('hide');
                    loadUsuarios();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al eliminar el usuario: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            });
        });
    });

    

    // Cargar roles y usuarios al iniciar
    cargarRoles();
    loadUsuarios();
});
</script>

<?php include_once 'includes/footer.php'; ?>