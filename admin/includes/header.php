<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

  <title>EduVault</title>
    <!-- Vincular el favicon -->
    <link rel="icon" type="image/png" href="img/logo.png" >


    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>



<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

      <!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar" style="background: linear-gradient(135deg, #2c3e50, #3498db); background-color: #2c3e50;">

<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
    <div class="sidebar-brand-icon" style="padding: 5px;">
        <img src="img/logo.png" alt="Logo Sistema de Asistencias" style="width: 135px; height: 135px; object-fit: contain; border-radius: 5px;">
    </div>
</a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Nav Item - Usuarios -->
    <li class="nav-item">
        <a class="nav-link" href="usuario.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Usuarios</span></a>
    </li>

   <!-- Nav Item - Reportes -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                   <span>Reportes</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="dropdown-item" href="reporte_asistencia.php">Reporte de Asistencias</a>
        <a class="dropdown-item" href="reporte_usuarios.php">Reporte de Usuarios</a>
                    </div>
                </div>
            </li>


    <!-- Nav Item - Horarios -->
    <li class="nav-item">
        <a class="nav-link" href="horario.php">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Horarios</span></a>
    </li>

    <!-- Nav Item - Justificaciones -->
    <li class="nav-item">
        <a class="nav-link" href="justificaciones.php">
            <i class="fas fa-fw fa-comment"></i>
            <span>Justificaciones</span></a>
    </li>

   <!-- Nav Item - Materiales -->
<li class="nav-item">
    <a class="nav-link" href="materiales.php">
        <i class="fas fa-fw fa-box"></i>
        <span>Materiales</span></a>
</li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>




                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                       

                        

                        <div class="topbar-divider d-none d-sm-block"></div>

                       <!-- Nav Item - User Information -->
<li class="nav-item dropdown no-arrow">
    <?php if (isset($_SESSION['autenticado_admin']) && $_SESSION['autenticado_admin'] === true): ?>
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
        </a>
        <!-- Dropdown - User Information -->
        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
            
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                Logout
            </a>
        </div>
    <?php else: ?>
        <!-- Fallback si no está autenticado como administrador -->
        <a class="nav-link" href="../login/login.php">Iniciar Sesión</a>
    <?php endif; ?>
</li>

                    </ul>

                </nav>
                <!-- End of Topbar -->