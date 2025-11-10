<?php
session_start();
include '../model/conexion.php';

// Limpiar sesiones de otros roles al iniciar
unset($_SESSION['autenticado_admin']);
unset($_SESSION['autenticado_profesor']);
unset($_SESSION['autenticado_auxiliar']);
unset($_SESSION['autenticado_alumno']);
unset($_SESSION['autenticado_padre']);

$error_message = '';

if (isset($_POST["btningresar"])) {
    // Sanitizar el campo usuario
    $usuario = htmlspecialchars(trim($_POST["usuario"]), ENT_QUOTES, 'UTF-8');
    $password = md5($_POST["password"]);

    try {
        $stmt = $conexion->prepare("SELECT u.id_usuario, u.usuario, u.password, u.nombre, u.apellido, r.nombre AS rol 
                                    FROM tabla_usuario u 
                                    INNER JOIN tabla_rol r ON u.id_rol = r.id_rol 
                                    WHERE u.usuario = :usuario");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado && $password === $resultado['password']) {
            // Establecer datos comunes en la sesión
            $_SESSION['id_usuario'] = $resultado['id_usuario'];
            $_SESSION['usuario'] = $resultado['usuario'];
            $_SESSION['nombre'] = $resultado['nombre'];
            $_SESSION['apellido'] = $resultado['apellido'];

            // Redirigir según el rol
            $rol = strtolower($resultado['rol']);
            switch ($rol) {
                case 'administrador':
                    $_SESSION['autenticado_admin'] = true;
                    header("Location: ../admin/index.php");
                    exit;
                case 'profesor':
                    $_SESSION['autenticado_profesor'] = true;
                    header("Location: ../profesor/index.php");
                    exit;
                default:
                    $error_message = "Rol no autorizado para este sistema";
                    break;
            }
        } else {
            $error_message = "Usuario o contraseña incorrectos";
        }
    } catch (PDOException $e) {
        $error_message = "Error en la autenticación: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LOGIN | EduVault</title>
  <link rel="icon" type="image/x-icon" href="img/logo.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- MDB UI Kit -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .divider:after,
    .divider:before {
      content: "";
      flex: 1;
      height: 1px;
      background: #eee;
    }
    .h-custom {
      height: calc(100% - 73px);
    }
    @media (max-width: 450px) {
      .h-custom {
        height: 100%;
      }
    }
    .password-container {
      position: relative;
    }
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
  </style>
</head>
<body>
  <section class="vh-100">
    <div class="container-fluid h-custom">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-md-9 col-lg-6 col-xl-5">
          <img src="img/logo.png" class="img-fluid" alt="Sample image">
        </div>
        <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
          <form id="loginForm" method="POST" action="">
            <div class="divider d-flex align-items-center my-4">
              <p class="text-center fw-bold mx-3 mb-0">Iniciar Sesion</p>
            </div>
            <!-- User input -->
            <div data-mdb-input-init class="form-outline mb-4">
              <input type="text" id="form3Example3" name="usuario" class="form-control form-control-lg"
                placeholder="Enter your username" />
              <label class="form-label" for="form3Example3">Usuario</label>
            </div>
            <!-- Password input -->
            <div data-mdb-input-init class="form-outline mb-3 password-container">
              <input type="password" id="form3Example4" name="password" class="form-control form-control-lg"
                placeholder="Enter password" />
              <i class="fas fa-eye password-toggle" id="togglePassword"></i>
              <label class="form-label" for="form3Example4">Password</label>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <!-- Checkbox -->
              <div class="form-check mb-0">
                <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
                <label class="form-check-label" for="form2Example3">
                  Remember me
                </label>
              </div>
            </div>
            <div class="text-center text-lg-start mt-4 pt-2">
              <button type="submit" name="btningresar" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
            </div>
          </form>
          <?php if (!empty($error_message)): ?>
              <script>
                  Swal.fire({
                      title: 'Error',
                      text: '<?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>',
                      icon: 'error',
                      confirmButtonText: 'Aceptar',
                      confirmButtonColor: '#3085d6'
                  });
              </script>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-primary">
      <!-- Copyright -->
      <div class="text-white mb-3 mb-md-0" id="copyright">
        Copyright © 2025. TechFusion Data
      </div>
      <!-- Right -->
      <div>
        <a href="https://www.facebook.com/TechFusionData" class="text-white me-4">
          <i class="fab fa-facebook-f"></i>
        </a>
      </div>
      <!-- Right -->
    </div>
  </section>
  <!-- Bootstrap JS and MDB UI Kit JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script>
  <script src="js/copyright.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('form3Example4');
      const icon = this;
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  </script>
</body>
</html>