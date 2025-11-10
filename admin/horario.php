<?php
session_start();
if (!isset($_SESSION['autenticado_admin']) || $_SESSION['autenticado_admin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
?>

<?php
include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Gestión de Horarios</h1>

    <!-- Tabla de Horarios -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Horario General</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form id="horarioForm" novalidate>
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora Entrada</th>
                                <th>Minutos Entrada</th>
                                <th>AM/PM</th>
                                <th>Hora Salida</th>
                                <th>Minutos Salida</th>
                                <th>AM/PM</th>
                            </tr>
                        </thead>
                        <tbody id="horarioTableBody">
                            <!-- Los días se cargarán dinámicamente -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary" id="guardarHorario">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = 'controllers/HorarioController.php';
    const form = document.getElementById('horarioForm');
    const tbody = document.getElementById('horarioTableBody');

    // Cargar días de la semana
    function cargarDias() {
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'listarDias'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                data.dias.forEach(dia => {
                    const isRequired = dia.id_dia <= 5 ? 'required' : '';
                    html += `
                        <tr>
                            <td>${dia.nombre}</td>
                            <td><input type="number" class="form-control" name="hora_entrada_${dia.id_dia}" min="0" max="23" ${isRequired}></td>
                            <td><input type="number" class="form-control" name="minuto_entrada_${dia.id_dia}" min="0" max="59" ${isRequired}></td>
                            <td>
                                <select class="form-control" name="entrada_am_pm_${dia.id_dia}" ${isRequired}>
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control" name="hora_salida_${dia.id_dia}" min="0" max="23" ${isRequired}></td>
                            <td><input type="number" class="form-control" name="minuto_salida_${dia.id_dia}" min="0" max="59" ${isRequired}></td>
                            <td>
                                <select class="form-control" name="salida_am_pm_${dia.id_dia}" ${isRequired}>
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
                cargarHorarios();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Error al cargar días',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar días: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    }

    // Cargar horarios generales
    function cargarHorarios() {
        // Limpiar campos
        for (let i = 1; i <= 7; i++) {
            const inputs = [
                `input[name="hora_entrada_${i}"]`,
                `input[name="minuto_entrada_${i}"]`,
                `select[name="entrada_am_pm_${i}"]`,
                `input[name="hora_salida_${i}"]`,
                `input[name="minuto_salida_${i}"]`,
                `select[name="salida_am_pm_${i}"]`
            ];
            inputs.forEach(selector => {
                const input = document.querySelector(selector);
                if (input) {
                    if (input.tagName === 'SELECT') {
                        input.value = 'AM'; // Valor por defecto
                    } else {
                        input.value = '';
                    }
                }
            });
        }

        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'obtenerHorarios'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.horarios.forEach(horario => {
                    const id_dia = horario.id_dia;
                    document.querySelector(`input[name="hora_entrada_${id_dia}"]`).value = horario.hora_entrada;
                    document.querySelector(`input[name="minuto_entrada_${id_dia}"]`).value = horario.minuto_entrada;
                    document.querySelector(`select[name="entrada_am_pm_${id_dia}"]`).value = horario.entrada_am_pm;
                    document.querySelector(`input[name="hora_salida_${id_dia}"]`).value = horario.hora_salida;
                    document.querySelector(`input[name="minuto_salida_${id_dia}"]`).value = horario.minuto_salida;
                    document.querySelector(`select[name="salida_am_pm_${id_dia}"]`).value = horario.salida_am_pm;
                });
            } else if (data.message !== 'No se encontraron horarios') {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Error al cargar horarios',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar horarios: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    }

    // Guardar horarios
    document.getElementById('guardarHorario').addEventListener('click', function(e) {
        e.preventDefault();

        // Validar campos requeridos para días laborables (1-5)
        let isValid = true;
        for (let i = 1; i <= 5; i++) {
            const horaEntrada = document.querySelector(`input[name="hora_entrada_${i}"]`).value;
            const minutoEntrada = document.querySelector(`input[name="minuto_entrada_${i}"]`).value;
            const entradaAmPm = document.querySelector(`select[name="entrada_am_pm_${i}"]`).value;
            const horaSalida = document.querySelector(`input[name="hora_salida_${i}"]`).value;
            const minutoSalida = document.querySelector(`input[name="minuto_salida_${i}"]`).value;
            const salidaAmPm = document.querySelector(`select[name="salida_am_pm_${i}"]`).value;

            if (!horaEntrada || !minutoEntrada || !entradaAmPm || !horaSalida || !minutoSalida || !salidaAmPm) {
                isValid = false;
                form.classList.add('was-validated');
                break;
            }
        }

        if (!isValid) {
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'guardarHorarios');

        fetch(ajaxUrl, {
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
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    cargarHorarios();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Error al guardar horarios: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    });

    // Inicializar
    cargarDias();
});
</script>

<?php
include 'includes/footer.php';
?>