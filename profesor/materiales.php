<?php
session_start();
if (!isset($_SESSION['autenticado_profesor']) || $_SESSION['autenticado_profesor'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
?>
<?php include_once 'includes/header.php'; ?>



<!-- [ Layout content ] Start -->
<div class="layout-content">
    <div class="container-fluid flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-0">Mis Materiales</h4>
        

        <div class="mb-4">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadMaterialModal">
                <i class="fas fa-upload mr-2"></i> Subir Material
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lista de Materiales Subidos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="materialesTable" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Archivo</th>
                                <th>Link</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="materialesTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para Subir Material -->
        <div class="modal fade" id="uploadMaterialModal" tabindex="-1" role="dialog" aria-labelledby="uploadMaterialModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadMaterialModalLabel">Subir Nuevo Material</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="uploadMaterialForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="archivo">Archivo (opcional)</label>
                                <input type="file" class="form-control-file" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.jpg,.png">
                            </div>
                            <div class="form-group">
                                <label for="link">Link (opcional)</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="https://ejemplo.com/video">
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Breve descripción del material"></textarea>
                            </div>
                            <input type="hidden" id="id_usuario" name="id_usuario" value="<?php echo $id_usuario; ?>">
                            <button type="submit" class="btn btn-primary btn-block">Subir</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Ver Material -->
        <div class="modal fade" id="viewMaterialModal" tabindex="-1" role="dialog" aria-labelledby="viewMaterialModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="viewMaterialModalLabel">Detalles del Material</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="viewMaterialContent">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const idUsuario = <?php echo $id_usuario; ?>;
    let dataTable;

    // AJAX universal para objetos y FormData
    function ajaxPost(action, data = {}) {
        let formData;
        if (data instanceof FormData) {
            formData = data;
            formData.append('action', action);
        } else {
            formData = new FormData();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
        }

        return fetch('controllers/MaterialControllerProfesor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                return result;
            } else {
                throw new Error(result.message || 'Error desconocido');
            }
        });
    }

    // Cargar materiales del profesor
    function loadMateriales() {
        ajaxPost('listarMaterialesPorProfesor', { id_usuario: idUsuario })
            .then(result => {
                const data = result.materiales;
                const tbody = document.getElementById('materialesTableBody');
                tbody.innerHTML = data.length === 0 ? '<tr><td colspan="6" class="text-center">No hay materiales subidos.</td></tr>' : 
                    data.map(material => `
                        <tr data-id="${material.id_material}" data-archivo="${material.archivo_url || ''}" data-link="${material.link || ''}">
                            <td>${material.id_material}</td>
                            <td>${material.archivo ? material.archivo : '-'}</td>
                            <td>${material.link ? material.link : '-'}</td>
                            <td>${material.descripcion || '-'}</td>
                            <td>${new Date(material.fecha).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-info btn-sm view-material" data-id="${material.id_material}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${material.archivo_url ? `<a href="${material.archivo_url}" class="btn btn-warning btn-sm" download><i class="fas fa-download"></i></a>` : ''}
                                ${material.link ? `<a href="${material.link}" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i></a>` : ''}
                            </td>
                        </tr>
                    `).join('');

                // Inicializar o actualizar DataTables
                if (dataTable) {
                    dataTable.destroy();
                }
                dataTable = $('#materialesTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                    },
                    pageLength: 10,
                    responsive: true,
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                });
            })
            .catch(err => {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar materiales: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            });
    }

    // Subir material
    document.getElementById('uploadMaterialForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        ajaxPost('subirMaterial', formData)
            .then(result => {
                Swal.fire({
                    title: 'Éxito',
                    text: result.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    $('#uploadMaterialModal').modal('hide');
                    form.reset();
                    loadMateriales();
                });
            })
            .catch(err => {
                Swal.fire({
                    title: 'Error',
                    text: err.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            });
    });

    // Ver material
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-material')) {
            e.preventDefault();
            const id = e.target.closest('.view-material').getAttribute('data-id');
            ajaxPost('obtenerMaterial', { id_material: id })
                .then(result => {
                    const mat = result.material;
                    document.getElementById('viewMaterialContent').innerHTML = `
                        <p><strong>ID:</strong> ${mat.id_material}</p>
                        <p><strong>Descripción:</strong> ${mat.descripcion || 'N/A'}</p>
                        <p><strong>Archivo:</strong> ${mat.archivo ? `<a href="${mat.archivo_url}" download>${mat.archivo}</a>` : 'N/A'}</p>
                        <p><strong>Link:</strong> ${mat.link ? `<a href="${mat.link}" target="_blank">${mat.link}</a>` : 'N/A'}</p>
                        <p><strong>Fecha:</strong> ${new Date(mat.fecha).toLocaleDateString()}</p>
                    `;
                    $('#viewMaterialModal').modal('show');
                })
                .catch(err => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al cargar detalles: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#3085d6'
                    });
                });
        }
    });

    // Iniciar
    loadMateriales();
});
</script>

<?php include_once 'includes/footer.php'; ?>