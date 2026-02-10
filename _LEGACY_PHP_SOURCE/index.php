<?php

include 'includes/session_check.php';

$rolUsuario = $_SESSION['user_role'];
$appsAccesibles = $_SESSION['apps_accesibles'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Apps BETA</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
		<!-- Bootstrap CSS -->
		<link href="/Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN">
        <!-- Core theme CSS-->
        <link href="fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container px-lg-5">
                <a class="navbar-brand" href="/">Apps BETA 1.0</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Inicio</a></li>
				        <li class="nav-item dropdown">
				          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownApps" role="button" data-bs-toggle="dropdown" aria-expanded="false">
				            Apps
				          </a>
				          <ul class="dropdown-menu">
				            <li><a class="dropdown-item" href="/Horas_Extras">Horas Extras</a></li>
				            <li><a class="dropdown-item" href="/Refrigerios">Refrigerios</a></li>
				            <li><hr class="dropdown-divider"></li>
				            <li><a class="dropdown-item" href="/Combustible">Combustible</a></li>
				          </ul>
				        </li>
                        <li class="nav-item"><a class="nav-link" href="/Contacto">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a></li>
                        <?php if ($rolUsuario == 'Adm_General'): ?>
                            <li class="nav-item"><a class="nav-link" href="/admin"><i class="fa-solid fa-screwdriver-wrench"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Header-->
        <header class="py-2">
            <div class="container px-lg-5">
                <div class="p-4 p-lg-5 bg-light rounded-3 text-center">
                    <div class="m-4 m-lg-5">
                        <h1 class="display-5 fw-bold">Apps BETA</h1>
                        <p class="fs-4">Gestiona los beneficios de los colaboradores de tu departamento.</p>
                        <a class="btn btn-primary btn-lg" href="#Empezar">Empezar</a>
                    </div>
                </div>
            </div>
        </header>
        <!-- Page Content-->
        <section class="pt-2">
            <div class="container px-lg-5">
                <!-- Page Features-->
                <div id="Empezar" class="row gx-lg-5" >
                    <div class="col-12 mb-5 text-center">
                        <h4>Apps Disponibles</h4>
                    </div>
                    <?php if (in_array('Horas_Extras', $appsAccesibles)): ?>
                        <div class="col-lg-6 col-xxl-4 mb-5">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center p-4 p-lg-5 pt-0 pt-lg-0">
                                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="fa-regular fa-hourglass-half"></i></div>
                                    <h2 class="fs-4 fw-bold">Horas Extras</h2>
                                    <p class="mb-0">Esta es la nueva verisón web del validador de horas extras. Aquí puedes generar, revisar y remitir a finanzas los reportes de horas extras de los colaboradores de tú departamento.</p>
                                    <br>
                                    <a class="btn btn-success btn-lg" href="/Horas_Extras">Abrir</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (in_array('Refrigerios', $appsAccesibles)): ?>
                    <div class="col-lg-6 col-xxl-4 mb-5">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4 p-lg-5 pt-0 pt-lg-0">
                                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="fa-solid fa-taxi"></i></div>
                                <h2 class="fs-4 fw-bold">Refrigerios y Taxis</h2>
                                <p class="mb-0">Esta es la nueva verisón web del validador de refrigerios. Aquí puedes generar, revisar y remitir los reportes de refrigerios de los colaboradores de tú departamento.</p>
                                <br>
                                <a class="btn btn-success btn-lg" href="/Refrigerios">Abrir</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (in_array('Combustible', $appsAccesibles)): ?>
                    <div class="col-lg-6 col-xxl-4 mb-5">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4 p-lg-5 pt-0 pt-lg-0">
                                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="fa-solid fa-gas-pump"></i></div>
                                <h2 class="fs-4 fw-bold">Visa Flotilla</h2>
                                <p class="mb-0">Esta app te ayudará a llegar un registro, seguimiento y control de las tarjetas de combustible (Visa Flotila) asignadas a los colaboradores de tu dirección regional.</p>
                                <br>
                                <a class="btn btn-success btn-lg" href="Combustible">Abrir</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; INFOTEP 2024</p></div>
        </footer>
        <!-- jQuery -->
		<script src="/Librerias/jquery v3.7.1/jquery-3.7.1.js"></script>
        <!-- Bootstrap core JS-->
		<script src="/Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="https://kit.fontawesome.com/7406913e7b.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
