<?php

include '../includes/session_check.php';

if ($_SESSION['user_role'] != 'Adm_General') {
    header('Location: ../index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Custom fonts for this template-->
    <link href="../Librerias/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Modal-->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">¡Oops!</h1>
            <i id="cerrarModal" class="fa-solid fa-x cerrarModal" role="button"></i>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <button id="" type="button" class="btn btn-primary cerrarModal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-secondary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="sidebar-brand-text mx-3">InfoApps <sup>Admin</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.html">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Panel</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Gestión
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fa-solid fa-user"></i>
                    <span>Usuarios</span>
                </a>
                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Administrar Usuarios</h6>
                        <a class="collapse-item active" href="#">Crear</a>
                        <a class="collapse-item" href="#">Reportes</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fa-solid fa-database"></i>
                    <span>Datos</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Administra los datos</h6>
                        <a class="collapse-item" href="nomina.php">Nómina</a>
                        <a class="collapse-item" href="Departamentos.php">Departamentos</a>
                        <a class="collapse-item" href="ponchado.php">Ponchado</a>
                        <a class="collapse-item" href="#">Calendario</a>
                        <a class="collapse-item" href="#">Horarios</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Reportes
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
<!--             <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li> -->

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Departamentos</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Individuales</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Sidebar Message -->
            <div class="sidebar-card d-none d-lg-flex">
                <img class="sidebar-card-illustration mb-2" src="img/exit.png" alt="...">
                <p class="text-center mb-2"><strong>Salir del panel</strong> hacia la galería de apps.</p>
                <a class="btn btn-success btn-sm" href="../">Ver apps</a>
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

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

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
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Jorge Tonos</span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfíl
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Registro de actividad
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Salir
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Usuarios</h1>
                        <!-- <button id="ActualizarNomina" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fa-solid fa-arrows-rotate fa-sm text-white-50"></i> Actualizar</button> -->
                        <input type="file" id="inputArchivoNomina" style="display: none;" accept=".xlsx"/>
                    </div>
                    <div id="Crear_Modificar_Form" class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Administrar Usuarios</h6>
                        </div>
                        
                        <div class="container mt-5">
                            <div class="row mb-3">
                                <div class="row col-6 align-items-center">
                                    <div class="col-8">
                                        <h2 id="FormTittle">Crear Nuevo Usuario</h2>
                                    </div>
                                    <div class="col-1">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="UserAdmMode" name="UserAdmMode">
                                            <label class="custom-control-label" for="UserAdmMode"></label>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <button type="reset" id="clearForm" class="btn btn-secondary btn-circle btn-md" type="button"><i class="fa-solid fa-arrow-rotate-left fa-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                            <form id="userForm" class="row">
                                <div class="col-2 mb-3">
                                    <label for="codigo">Código</label>
                                    <Select class="form-control search-select" id="codigo" name="codigo" required></Select>
                                </div>
                                <div class="col-5 mb-3">
                                    <label for="nombre">Nombre</label>
                                    <Select class="form-control search-select" id="nombre" name="nombre" required></Select>
                                </div>
                                <div class="col-3 mb-3">
                                    <label for="cedula">Cédula</label>
                                    <Select class="form-control search-select" id="cedula" name="cedula"></Select>
                                </div>
                                <div class="col-3 mb-3">
                                    <label for="cargo">Cargo</label>
                                    <input type="text" class="form-control " id="cargo" name="cargo" readonly>
                                </div>
                                <div class="col-2 mb-3">
                                    <label for="gerencia">Gerencia</label>
                                    <select class="custom-select form-control " id="gerencia" name="gerencia">
                                        <option value="">Seleccionar</option>
                                    </select>
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="departamento">Departamento</label>
                                    <select class="custom-select form-control " id="departamento" name="departamento" required>
                                        <option value="">Seleccionar</option>
                                    </select>
                                </div>
                                <div class="col-2 mb-3">
                                    <label for="sueldo">Sueldo</label>
                                    <input type="number" class="form-control " id="sueldo" name="sueldo" readonly>
                                </div>
                                <div class="col-1 mb-3">
                                    <label for="nivelCorporativo">Nivel</label>
                                    <select class="custom-select form-control " id="nivelCorporativo" name="nivelCorporativo" required>
                                        <option value="" disabled selected></option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="correo">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="fdetal@infotep.gob.do" required>
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="contrasena">Contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                </div>
                                <div class="col-2 mb-3">
                                    <label for="rol" class="form-label">Rol</label>
                                    <select class="custom-select  form-control " id="rol" name="rol" required>
                                        <option value="" disabled selected>Seleccionar</option>
                                    </select>
                                </div>
                                <div id="apps" class="col-2 mb-3">
                                    <label for="apps" class="form-label">Apps</label>
                                    <!-- Se añaden automáticamente -->
                                </div>
                                <div class="row col-12 mb-3">
                                    <div class="col 12 text-center">
                                        <p class="h3 text-center mb-0">Horario</p>
                                    </div>
                                    <div class="col-12 text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="switchHorario" name="tipoHorario">
                                            <label class="custom-control-label" for="switchHorario">Rotativo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-12 mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Horario Base</label>
                                        <div class="row">
                                            <div class="col">
                                                <label class="form-label" for="lunes">Lunes <i id="completarHr" class="fa-solid fa-forward ml-2" role="button"></i></label>
                                                <input type="time" class="form-control input-hora-dia" id="lunes" name="lunes" placeholder="Lunes">
                                            </div>
                                            <div class="col">
                                                <label class="form-label" for="martes">Martes</label>
                                                <input type="time" class="form-control input-hora-dia" id="martes" name="martes" placeholder="Martes">
                                            </div>
                                            <div class="col">
                                                <label class="form-label" for="miercoles">Miercoles</label>
                                                <input type="time" class="form-control input-hora-dia" id="miercoles" name="miercoles" placeholder="Miércoles">
                                            </div>
                                            <div class="col">
                                                <label class="form-label" for="jueves">Jueves</label>
                                                <input type="time" class="form-control input-hora-dia" id="jueves" name="jueves" placeholder="Jueves">
                                            </div>
                                            <div class="col">
                                                <label class="form-label" for="viernes">Viernes</label>
                                                <input type="time" class="form-control input-hora-dia" id="viernes" name="viernes" placeholder="Viernes">
                                            </div>
                                            <div class="col">
                                                <label class="form-label" for="sabado">sabado</label>
                                                <input type="time" class="form-control input-hora-dia" id="sabado" name="sabado" placeholder="Sábado">
                                            </div>
                                            <div class="col-2 mt-2">
                                                <label class="form-label" for="domingo">Domingo</label>
                                                <input type="time" class="form-control input-hora-dia" id="domingo" name="domingo" placeholder="Domingo">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-6 mb-4">
                                    <lable class="form-label">Nombre en firma de papelería</lable>
                                    <input type="text" class="form-control" id="preferencia_firma" name="preferenica_firma">
                                </div>
                                <div class="col-6 mb-4 row">
                                    <div class="col-12 text-right px-0 align-self-end">
                                        <button id="eliminarUsuario" class="btn btn-danger" style="display: none;"><i class="fa-solid fa-circle-minus mr-2"></i>Eliminar</button>
                                        <button id="crearUsuario" class="btn btn-primary"><i class="fa-solid fa-circle-plus mr-2"></i>Crear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://kit.fontawesome.com/7406913e7b.js" crossorigin="anonymous"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>