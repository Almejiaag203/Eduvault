<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcar Asistencia - Sistema de Asistencia</title>

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="admin/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Marcar Asistencia</h1>
                                        <div class="clock-container mb-4">
                                            <div class="clock" id="digitalClock">
                                                <div class="date" id="currentDate"></div>
                                                <div class="time" id="currentTime"></div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <p>Bienvenido: <span id="nombreUsuario">Nadie</span></p>
                                        </div>
                                    </div>
                                    <form class="user" id="marcarAsistenciaForm">
                                        <div class="form-group">
                                            <label for="codigo_asistencia">Código de Asistencia</label>
                                            <input type="text" class="form-control form-control-user" id="codigo_asistencia" name="codigo_asistencia" placeholder="Ingrese su código de asistencia" required>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-user btn-block" id="marcarBoton">
                                            <i class="fas fa-sign-in-alt"></i> Marcar Entrada
                                        </button>
                                        <button type="button" class="btn btn-info btn-user btn-block" id="escanearQR">
                                            <i class="fas fa-qrcode"></i> Escanear QR
                                        </button>
                                    </form>
                                    <div id="videoContainer" class="mt-3" style="display: none; text-align: center;">
                                        <video id="video" width="100%" height="auto" autoplay playsinline></video>
                                        <canvas id="canvas" style="display: none;"></canvas>
                                        <button type="button" class="btn btn-danger btn-user btn-block mt-2" id="detenerEscaneo">
                                            <i class="fas fa-stop"></i> Detener Escaneo
                                        </button>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <p>Última marcación: <span id="ultimaMarcacion">Ninguna</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright © <?php echo date('Y'); ?> <a href="https://www.facebook.com/TechFusionData" target="_blank" rel="noopener noreferrer">TechFusion Data</a></span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

    <!-- Bootstrap core JavaScript -->
    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript -->
    <script src="admin/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages -->
    <script src="admin/js/sb-admin-2.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jsQR library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

    <!-- Custom script for digital clock, QR scanning, and attendance marking -->
    <script>
        $(document).ready(function() {
            const ajaxUrl = 'controllers/AsistenciaController.php';
            let videoStream = null;

            // Función para actualizar el reloj digital (zona horaria de Perú)
            function updateDigitalClock() {
                const now = new Date();
                const options = { timeZone: 'America/Lima', hour12: true };
                const dateOptions = { 
                    timeZone: 'America/Lima', 
                    weekday: 'long', 
                    day: 'numeric', 
                    month: 'long', 
                    year: 'numeric' 
                };

                const formattedDate = now.toLocaleDateString('es-PE', dateOptions);
                const formattedTime = now.toLocaleTimeString('es-PE', options);

                $('#currentDate').text(formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1));
                $('#currentTime').text(formattedTime);
            }

            updateDigitalClock();
            setInterval(updateDigitalClock, 1000);

            // Función para obtener el estado del botón (entrada/salida)
            function obtenerEstadoBoton() {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: { action: 'obtenerEstadoBoton' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const marcarBoton = $('#marcarBoton');
                            if (response.esHoraSalida) {
                                marcarBoton.removeClass('btn-primary').addClass('btn-danger');
                                marcarBoton.html('<i class="fas fa-sign-out-alt"></i> Marcar Salida');
                            } else {
                                marcarBoton.removeClass('btn-danger').addClass('btn-primary');
                                marcarBoton.html('<i class="fas fa-sign-in-alt"></i> Marcar Entrada');
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al obtener el estado del botón',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }

            // Función para limpiar el formulario después de 3 segundos
            function limpiarFormulario() {
                setTimeout(function() {
                    $('#codigo_asistencia').val('');
                    $('#nombreUsuario').text('Nadie');
                    $('#ultimaMarcacion').text('Ninguna');
                }, 3000);
            }

            // Función para verificar la marcación actual
            function verificarEstadoMarcacion(codigo) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'verificarMarcacion',
                        codigo_asistencia: codigo
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#nombreUsuario').text(response.nombre + ' ' + response.apellido);
                            if (response.ultima_marcacion) {
                                $('#ultimaMarcacion').text(response.ultima_marcacion);
                            } else {
                                $('#ultimaMarcacion').text('Ninguna');
                            }
                            limpiarFormulario();
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                            $('#nombreUsuario').text('Nadie');
                            $('#ultimaMarcacion').text('Ninguna');
                            $('#codigo_asistencia').val('');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al verificar marcación',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#nombreUsuario').text('Nadie');
                        $('#ultimaMarcacion').text('Ninguna');
                        $('#codigo_asistencia').val('');
                    }
                });
            }

            // Función para marcar asistencia (entrada o salida)
            function marcarAsistencia(codigo) {
                if (!codigo) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor, ingrese o escanee un código de asistencia.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }

                const isSalida = $('#marcarBoton').hasClass('btn-danger');
                const action = isSalida ? 'marcarSalida' : 'marcarEntrada';

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: action,
                        codigo_asistencia: codigo
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Éxito',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            });
                            verificarEstadoMarcacion(codigo);
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                            $('#codigo_asistencia').val('');
                            $('#nombreUsuario').text('Nadie');
                            $('#ultimaMarcacion').text('Ninguna');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al procesar la marcación',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#codigo_asistencia').val('');
                        $('#nombreUsuario').text('Nadie');
                        $('#ultimaMarcacion').text('Ninguna');
                    }
                });
            }

            // Acción del botón de marcación manual
            $('#marcarBoton').on('click', function() {
                const codigo = $('#codigo_asistencia').val();
                marcarAsistencia(codigo);
            });

            // Validar al presionar Enter
            $('#codigo_asistencia').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#marcarBoton').click();
                }
            });

            // Función para iniciar el escaneo de QR
            $('#escanearQR').on('click', function() {
                $('#videoContainer').show();
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(function(stream) {
                        videoStream = stream;
                        const video = document.getElementById('video');
                        video.srcObject = stream;
                        video.play();

                        const canvas = document.getElementById('canvas');
                        const context = canvas.getContext('2d');

                        function tick() {
                            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                                canvas.height = video.videoHeight;
                                canvas.width = video.videoWidth;
                                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                                    inversionAttempts: 'dontInvert'
                                });

                                if (code) {
                                    $('#codigo_asistencia').val(code.data);
                                    detenerEscaneo();
                                    marcarAsistencia(code.data);
                                    return;
                                }
                            }
                            requestAnimationFrame(tick);
                        }
                        requestAnimationFrame(tick);
                    })
                    .catch(function(err) {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo acceder a la cámara: ' + err.message,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#videoContainer').hide();
                    });
            });

            // Función para detener el escaneo
            function detenerEscaneo() {
                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
                    videoStream = null;
                }
                $('#videoContainer').hide();
            }

            $('#detenerEscaneo').on('click', detenerEscaneo);

            // Actualizar estado del botón cada 5 minutos
            setInterval(obtenerEstadoBoton, 300000); // 5 minutos
            obtenerEstadoBoton(); // Inicial
        });
    </script>

    <!-- Estilos personalizados para el reloj digital y video -->
    <style>
        .clock-container {
            margin: 20px 0;
        }
        .clock {
            background: linear-gradient(145deg, #f0f0f0, #e6e6e6);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: 2px solid #ddd;
            max-width: 400px;
            margin: 0 auto;
        }
        .date {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .time {
            font-size: 2.5em;
            font-weight: 700;
            color: #007bff;
            text-align: center;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        #videoContainer {
            max-width: 400px;
            margin: 0 auto;
        }
        #video {
            border-radius: 10px;
            border: 2px solid #007bff;
        }
    </style>
</body>
</html>