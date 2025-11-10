<?php
// admin/materiales.php

session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
?>
<?php include_once 'includes/header.php'; ?>

<!-- [ Layout content ] Start -->
<div class="layout-content">
    <!-- [ content ] Start -->
    <div class="container-fluid flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-0 text-primary">Gestión de Materiales</h4>
       

        <!-- Contenedor de carpetas de profesores (nivel superior) -->
        <div id="professorFolders">
            <h5 class="mb-4 font-weight-bold text-primary">
                <i class="fas fa-chalkboard-teacher mr-2"></i> Profesores
            </h5>
            <div class="row" id="professorRow">
                <!-- Se carga dinámicamente via AJAX -->
            </div>
        </div>

        <!-- Contenedor para materiales (nivel 2: directo bajo profesor, sin semestres) -->
        <div id="materialesContainer" style="display: none;">
            <div class="mb-3">
                <button class="btn btn-outline-primary btn-sm shadow-sm" id="backToProfessors">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Profesores
                </button>
            </div>
            <h5 class="mb-3 font-weight-bold text-info" id="currentProfessorTitle"></h5>
            <div class="p-4 bg-white border rounded shadow-sm" id="materialesTabContent">
                <!-- Tabla de materiales se carga aquí dinámicamente -->
            </div>
            <button class="btn btn-outline-danger btn-sm mt-3 shadow-sm" id="closeTab">
                <i class="fas fa-times mr-1"></i> Cerrar Lista
            </button>
        </div>

        <!-- Modal dinámico para Ver Material -->
        <div class="modal fade" id="viewMaterialModal" tabindex="-1" role="dialog" aria-labelledby="viewMaterialModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-gradient-info text-white">
                        <h5 class="modal-title" id="viewMaterialModalLabel">
                            <i class="fas fa-file-alt mr-2"></i> Detalles del Material
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center">
                            <i class="fas fa-file-alt fa-4x mb-3 text-primary"></i>
                            <h5 id="viewMaterialTitle" class="font-weight-bold">Material</h5>
                        </div>
                        <div class="mt-3">
                            <p><strong>ID Material:</strong> <span id="view_id_material"></span></p>
                            <p><strong>Profesor:</strong> <span id="view_profesor"></span></p>
                            <p><strong>Fecha:</strong> <span id="view_fecha"></span></p>
                            <p><strong>Archivo/Link:</strong> <a id="view_archivo" href="#" target="_blank">Abrir/Descargar</a></p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- [ content ] End -->
</div>
<!-- [ Layout content ] End -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const professorRow = document.getElementById('professorRow');
    const materialesTabContent = document.getElementById('materialesTabContent');
    const currentProfessorTitle = document.getElementById('currentProfessorTitle');

    let currentProfessorId = null;
    let currentProfesorNombre = '';

    // Función para POST AJAX al controlador
    function ajaxPost(action, data = {}) {
        return new Promise((resolve, reject) => {
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }

            fetch('controllers/MaterialController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    resolve(result);
                } else {
                    reject(new Error(result.message || 'Error desconocido'));
                }
            })
            .catch(reject);
        });
    }

    // Mejorar diseño de cards de profesores
    function loadProfesores() {
        professorRow.innerHTML = '<div class="col-12 text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando profesores...</div>';
        ajaxPost('listarProfesores')
            .then(result => {
                const profesores = result.profesores;
                const row = document.createElement('div');
                row.className = 'row justify-content-center';
                if (profesores.length === 0) {
                    row.innerHTML = '<div class="col-12"><div class="alert alert-info">No hay profesores registrados.</div></div>';
                } else {
                    profesores.forEach(prof => {
                        const col = document.createElement('div');
                        col.className = 'col-lg-4 col-md-6 col-sm-12 mb-4 d-flex align-items-stretch';
                        col.innerHTML = `
                            <div class="card shadow-sm border-0 w-100 h-100" style="cursor: pointer;" data-id="${prof.id_usuario}" data-nombre="${prof.nombre_completo}">
                                <div class="card-body text-center p-4">
                                    <div class="bg-primary rounded-circle d-inline-block mb-3 p-3">
                                        <i class="fas fa-user-tie fa-3x text-white"></i>
                                    </div>
                                    <h5 class="card-title font-weight-bold text-dark">${prof.nombre_completo}</h5>
                                    <p class="card-text text-muted mb-0">Profesor</p>
                                    <p class="card-text text-muted mb-0">Aula: ${prof.aula || 'No asignada'}</p>
                                </div>
                            </div>
                        `;
                        row.appendChild(col);
                    });
                }
                professorRow.innerHTML = '';
                professorRow.appendChild(row);
            })
            .catch(err => {
                console.error('Error cargando profesores:', err);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar los profesores: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            });
    }

    // Cargar materiales por profesor
    function loadMateriales(profId, profNombre) {
        currentProfessorId = profId;
        currentProfesorNombre = profNombre;
        currentProfessorTitle.textContent = `Materiales de ${profNombre}`;
        materialesTabContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando materiales...</div>';
        ajaxPost('listarMaterialesPorProfesor', { id_usuario: profId })
            .then(result => {
                const data = result.materiales;
                materialesTabContent.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered rounded shadow-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Archivo/Link</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.length === 0 ? '<tr><td colspan="4" class="text-center">No hay materiales registrados.</td></tr>' : 
                                data.map(material => `
                                    <tr data-id="${material.id_material}" data-archivo="${material.archivo_url || ''}" data-link="${material.link || ''}">
                                        <td class="text-center align-middle">${material.id_material}</td>
                                        <td class="align-middle">${material.archivo ? material.archivo : (material.link ? 'Enlace Externo' : 'Sin contenido')}</td>
                                        <td class="align-middle">${new Date(material.fecha).toLocaleDateString()}</td>
                                        <td class="text-center align-middle">
                                            <button class="btn btn-info btn-sm view-material mr-1 shadow-sm" data-id="${material.id_material}" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            ${material.archivo_url ? `<a href="${material.archivo_url}" class="btn btn-warning btn-sm mr-1 shadow-sm" download title="Descargar"><i class="fas fa-download"></i></a>` : ''}
                                            ${material.link ? `<a href="${material.link}" class="btn btn-primary btn-sm shadow-sm" target="_blank" title="Abrir enlace"><i class="fas fa-external-link-alt"></i></a>` : ''}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            })
            .catch(err => {
                materialesTabContent.innerHTML = '<div class="alert alert-danger">Error cargando materiales: ' + err.message + '</div>';
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar los materiales: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                });
            });
    }

    // Eventos
    document.addEventListener('click', function(e) {
        // Click en card de profesor
        const profCard = e.target.closest('.card[data-id]');
        if (profCard) {
            const profId = profCard.getAttribute('data-id');
            const profNombre = profCard.getAttribute('data-nombre');
            document.getElementById('professorFolders').style.display = 'none';
            document.getElementById('materialesContainer').style.display = 'block';
            loadMateriales(profId, profNombre);
        }

        // Ver material
        if (e.target.closest('.view-material')) {
            e.preventDefault();
            const id = e.target.closest('.view-material').getAttribute('data-id');
            ajaxPost('obtenerMaterial', { id_material: id })
                .then(result => {
                    const mat = result.material;
                    document.getElementById('viewMaterialTitle').textContent = `Material #${mat.id_material}`;
                    document.getElementById('view_id_material').textContent = mat.id_material;
                    document.getElementById('view_profesor').textContent = mat.profesor_nombre;
                    document.getElementById('view_fecha').textContent = new Date(mat.fecha).toLocaleDateString();
                    
                    // Enlace dinámico: archivo o link
                    const linkEl = document.getElementById('view_archivo');
                    if (mat.archivo_url) {
                        linkEl.textContent = 'Descargar Archivo';
                        linkEl.href = mat.archivo_url;
                        linkEl.download = mat.archivo;
                        linkEl.target = '_blank';
                    } else if (mat.link) {
                        linkEl.textContent = 'Abrir Link';
                        linkEl.href = mat.link;
                        linkEl.target = '_blank';
                        linkEl.removeAttribute('download');
                    } else {
                        linkEl.textContent = 'Sin enlace disponible';
                        linkEl.href = '#';
                        linkEl.style.pointerEvents = 'none';
                    }
                    $('#viewMaterialModal').modal('show');
                })
                .catch(err => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error cargando detalles del material: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#3085d6'
                    });
                });
        }
    });

    // Botones de navegación
    document.getElementById('backToProfessors').addEventListener('click', function() {
        document.getElementById('professorFolders').style.display = 'block';
        document.getElementById('materialesContainer').style.display = 'none';
        loadProfesores();
    });

    document.getElementById('closeTab').addEventListener('click', function() {
        document.getElementById('professorFolders').style.display = 'block';
        document.getElementById('materialesContainer').style.display = 'none';
        loadProfesores();
    });

    // Iniciar cargando profesores
    loadProfesores();
});
</script>

<?php include_once 'includes/footer.php'; ?>