<?php
session_start();
include '../includes/session_check.php';
$nombreAppActual = 'Horas_Extras'; // El nombre de esta aplicación específica

if (!in_array($nombreAppActual, $_SESSION['apps_accesibles'])) {
    // El usuario no tiene acceso a esta aplicación
    // Redirigir o mostrar mensaje de error
  header('Location: ../index.php');
  exit;
}

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
  <title>Horas Extras</title>
  <!-- Bootstrap v5.3-->
  <link href="../Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Selec2-->
  <link href="../Librerias/Select2/select2-4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Core theme CSS-->
  <link href="../Librerias/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../Librerias/Flatpickr/flatpickr-master/src/flatpickr.min.css">
  <link rel="stylesheet" href="../Librerias/animate.css-main/animate.min.css" />
  <link href="css/styles.css" rel="stylesheet" />
</head>
<body>
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
          <button type="button" class="btn btn-primary cerrarModal">OK</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Responsive navbar-->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container px-lg-5">
      <a class="navbar-brand" href="/">Apps BETA 1.0</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="/">Inicio</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownApps" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Apps
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Horas Extras</a></li>
              <li><a class="dropdown-item" href="/Refrigerios">Refrigerios</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/Combustible">Combustible</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="/Contacto">Contacto</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Header-->
  <header class="py-4">
    <div class="container">
      <div class="row text-center mt-4">
        <div class="col-12 mt-4">
          <h1 class="display-5 fw-bold">Horas Extras</h1>
        </div>
      </div>
    </div>
  </header>
  <!-- Page Content-->
  <div class="container px-lg-5">
    <div class="row justify-content-center align-items-center">
      <div class="row col-7 justify-content-between align-items-center">
        <div class="col-2 text-left mb-3">
          <i id="btnAnterior" class="fa-solid fa-caret-left fa-2xl icon-animate animate__animated" role="button" style="font-size: 80px; display: none;"></i>
        </div>
        <div class="col-8 mb-3 text-center" style="font-size: 20px;">
          <p class="mb-0">Genera y envía tus resportes de horas extras a la administración</p>
        </div>
        <div class="col-2 text-end mb-3">
          <div>
            <i id="btnSiguiente" class="fa-solid fa-caret-right fa-2xl icon-animate animate__animated" role="button" style="font-size: 80px; display: none;"></i>
          </div>

        </div>
      </div>
    <?php if ($rolUsuario != 'Colaborador'): ?>
      <div id="PanelInicial" class="row col-9 text-center animate__animated">
        <div class="card">
          <div class="card-body">
            <div id="administrarReportes" class="row">
              <div class="container">
                <h2 class="CardTtl">Administrar Reportes</h2>
                <p>1Q Mayo - 2024</p>
                <div class="row">
                  <!-- Indicador: Horas Reportadas -->
                  <div class="col-md-3 mb-3">
                    <div class="card fw-bold">
                      <div class="card-header">Horas Reportadas</div>
                      <div class="card-body">
                        <h5 class="card-title" id="totalHours">...</h5>
                        <p class="card-text"><i class="fas fa-business-time"></i></p>
                      </div>
                    </div>
                  </div>

                  <!-- Indicador: Cantidad de Empleados -->
                  <div class="col-md-3 mb-3">
                    <div class="card fw-bold">
                      <div class="card-header">Empleados</div>
                      <div class="card-body">
                        <h5 class="card-title" id="totalEmployees">...</h5>
                        <p class="card-text"><i class="fas fa-users"></i></p>
                      </div>
                    </div>
                  </div>

                  <!-- Indicador: Total a Pagar -->
                  <div class="col-md-3 mb-3">
                    <div class="card fw-bold">
                      <div class="card-header">Total a Pagar</div>
                      <div class="card-body">
                        <h5 class="card-title" id="totalPay">...</h5>
                        <p class="card-text"><i class="fas fa-dollar-sign"></i></p>
                      </div>
                    </div>
                  </div>

                  <!-- Indicador: Reportes con Objecciones -->
                  <div class="col-md-3 mb-3">
                    <div class="card fw-bold">
                      <div class="card-header">Objeciones</div>
                      <div class="card-body">
                        <h5 class="card-title" id="reportsWithObjections">...</h5>
                        <p class="card-text"><i class="fas fa-exclamation-triangle"></i></p>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Nav tabs -->
                <div class="row">
                  <div class="col-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Detalle Lote</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="objeciones-tab" data-bs-toggle="tab" data-bs-target="#objeciones" type="button" role="tab" aria-controls="objeciones" aria-selected="false">Objeciones</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab" aria-controls="historial" aria-selected="false">Buscar</button>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Tab panes -->
                <div class="row tab-content pt4">
                  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <!-- Lista de Empleados en Lote Pendiente (Visible por defecto) -->
                    <div id="listaEmpleados" class="pt-2 border border-top-0 border-bottom-0">
                      <div class="col-12 text-start px-1">
                        <span type="button" class="backBtn px-3 invisible"><i class="fa-solid fa-circle-left" style="font-size: 30px;"></i></span>
                      </div>
                      <div class="col-12 pt-2">
                        <h3>Lote Seleccionado</h3>
                        <p>1Q Mayo - 2024</p>
                      </div>
                      <div class="row mb-1">
                        <div class="col-12 text-center">
                          <button id="crearReporte" type="button" class="btn btn-outline-success btn-sm fw-bold"><i class="fa-solid fa-plus"></i> Nuevo Reporte</button>
                          <button id="buscarLote" type="button" class="btn btn-outline-success btn-sm fw-bold"><i class="fa-solid fa-magnifying-glass"></i> Buscar Lote</button>
                        </div>
                      </div>
                      <div class="col-12">
                        <table class="table">
                          <thead>
                            <tr>
                              <th>Código</th>
                              <th>Nombre</th>
                              <th>Horas Extras</th>
                              <th>Total a Pagar</th>
                              <th>Estado</th>
                            </tr>
                          </thead>
                          <tbody id="empleadosLotePendiente">
                            <!-- Los datos de empleados se insertarán aquí -->
                          </tbody>
                        </table> 
                      </div>
                    </div>

                    <!-- Historial de Lotes -->
                    <div id="historialLotes" class="pt-2 border border-top-0 border-bottom-0" style="display: none;">
                      <div class="col-12 text-start px-1">
                        <span type="button" class="backBtn px-3"><i class="fa-solid fa-circle-left" style="font-size: 30px;"></i></span>
                      </div>
                      <div class="col-12 pt-2">
                        <h3>Historial de Lotes</h3>
                        <p>2024</p>
                      </div>
                      <div class="col-12">
                        <div class="dropdown mb-2">
                          <button class="selectorPeriodoLotes btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                            Selecciona un período
                          </button>
                          <ul id="listaLotesDropdown" class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                            <!-- Las opciones del dropdown se llenarán dinámicamente con JS -->
                            <li><a class="dropdown-item" href="#" data-periodo="2024">2024</a></li>
                            <li><a class="dropdown-item" href="#" data-periodo="2023">2023</a></li>
                            <li><a class="dropdown-item" href="#" data-periodo="2022">2022</a></li>
                            <li><a class="dropdown-item" href="#" data-periodo="2021">2021</a></li>
                            <li><a class="dropdown-item" href="#" data-periodo="2020">2020</a></li>
                            <li><a class="dropdown-item" href="#" data-periodo="...">...</a></li>
                          </ul>
                        </div>
                        <div class="col-12">
                          <table class="table">
                            <thead>
                              <tr>
                                <th>Periodo</th>
                                <th>Fecha Corte</th>
                                <th>Estado</th>
                                <th>Total Pagado</th>
                              </tr>
                            </thead>
                            <tbody id="loteTableBody">
                              <!-- Los lotes se cargarán aquí dinámicamente -->
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">

                  </div>
                  <div class="tab-pane fade" id="objeciones" role="tabpanel" aria-labelledby="objeciones-tab">
                    <div id="objeciones" class="pt-2 border border-top-0 border-bottom-0">
                      <div class="col-12">
                        <div class="col-12 text-start px-1">
                          <span type="button" class="backBtn px-3 invisible"><i class="fa-solid fa-circle-left" style="font-size: 30px;"></i></span>
                        </div>
                        <div class="col-12 pt-2">
                          <h3 class="mb-4">Objeciones</h3>
                        </div>
                        <div class="col-12">
                          <table class="table">
                            <thead>
                              <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Total Horas</th>
                                <th>Objeciones</th>
                              </tr>
                            </thead>
                            <tbody id="objecionesLotePendiente">
                              <!-- Los datos de empleados se insertarán aquí -->
                            </tbody>
                          </table>
                        </div> 
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="SelectEmpleado" class="row SeccionRep col-9 text-center animate__animated" style="display: none;">
        <div class="card">
          <div class="card-body">
            <div id="buscarEmpleado">
              <div class="row">
                <div class="col-12">
                  <h2 class="CardTtl card-title text-center">Buscar Empleado</h2>
                </div>
              </div>
              <div class="row justify-content-center">
                <div class="col-8">
                  <div class="text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3 p-1"><em>Selecciona a un empleado de la lista y verifica sus informaciones antes de empezar a trabajar</em></div>
                </div>
              </div>
              <div class="row align-items-center pt-3">
                <div class="col-4">

                </div>
                <div class="row col-8 align-items-center justify-content-center">
                  <div class="col-2 text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-5" style="font-size: 30px;">
                    <i class="fa-solid fa-magnifying-glass"></i> 
                  </div>
                  <div class="col-10">
                    <Select class="form-control search-select" id="dbSearch" name="nombre" required></Select>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <div class="col-4">
                  <img src="img/Binoculares2.png" class="img-fluid" alt="...">
                </div>
                <div class="row col-8 justify-content-center">
                  <div class="row mt-2 text-start">
                    <div class="card">
                      <div id="srchCardEmpty" class="card-body">
                        <!-- Carousel para Tips -->
                        <div id="carouselTips" class="carousel slide carousel-dark" data-bs-ride="carousel">
                          <div class="carousel-indicators mt-2">
                            <button type="button" data-bs-target="#carouselTips" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselTips" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselTips" data-bs-slide-to="2" aria-label="Slide 3"></button>
                          </div>
                          <div class="carousel-inner text-justify">
                            <div class="carousel-item active">
                              <div class="border border-5 border-warning-subtle rounded rounded-4 p-2">
                                <div class="row">
                                  <h6 class="fw-bold">Verifica los datos del colaborador seleccionado</h6>
                                  <p class="carousel-Text">Antes de hacer un reporte de horas extras, revisa bien el sueldo y horario del empleado. Si algo no está bien, pide a la administración que lo corrija primero. Así evitamos errores en los reportes.</p>
                                </div>
                              </div>
                            </div>
                            <div class="carousel-item">
                              <div class="border border-5 border-warning-subtle rounded rounded-4 p-2">
                                <div class="row">
                                  <h6 class="fw-bold">Adjunta los soportes necesarios</h6>
                                  <p class="carousel-Text">Debes asegurarte de adjuntar todos los soportes necesarios para la remisión de la solicitud de pago de horas extras a la administración. Esto incluye: </p><p class="carousel-Text mt-2"><span><i class="fa-regular fa-circle-dot"></i> Relación de totales</span> <span><i class="fa-regular fa-circle-dot"></i> Excepciones de ponchado</span> <span><i class="fa-regular fa-circle-dot"></i> Reportes de ponchado</span> <span><i class="fa-regular fa-circle-dot"></i> Reportes detallados de horas extras</span></p>
                                </div>
                              </div>
                            </div>
                            <div class="carousel-item">
                              <div class="border border-5 border-warning-subtle rounded rounded-4 p-2">
                                <div class="row">
                                  <h6 class="fw-bold">Enviar antes del día 5</h6>
                                  <p class="carousel-Text">Las solicitudes de pago de horas extras deben llegar a la ONA a más tardar los día 5 de cada mes para poder ser tomadas en cuenta para pago en la primera quincena de ese mismo mes.</p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div id="srchCard" class="card-body d-none">
                        <div class="col-12">
                          <blockquote class="blockquote">
                            <span class="Nombre"><strong>Nombre - ####</strong></span>
                          </blockquote>
                        </div>
                        <div class="col-12">
                          <figcaption class="blockquote-footer mb-2 Cargo">
                            Cargo que ocupa
                          </figcaption>
                        </div>
                        <div class="col-12 Dependencia">
                          <span>Departamento</span>
                          <br>
                        </div>
                        <div class="col-12">
                          <span class="Sueldo"><small><em>RD$00,000.00</em></small></span>
                        </div>
                        <div class="col-12 Gerencia">
                          <span>Gerencia</span>
                        </div>
                        <div class="col-12 text-center">
                          <h6><strong>Horario</strong></h6>
                        </div>
                        <hr class="opacity-50 my-2" style="border-width: 3px; border-color: #023047; border-radius: 2rem">
                        <div class="row px-2 text-center align-items-center dayNames">
                          <div class="col border border-success-subtle rounded-3 border-3 mx-left-3 text-success domingo"><span class="fw-bold">D</span></div>
                          <div class="col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3 lunes"><span>L</span></div>
                          <div class="col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3 martes"><span>M</span></div>
                          <div class="col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3 miercoles"><span>X</span></div>
                          <div class="col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3 jueves"><span>J</span></div>
                          <div class="col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3 viernes"><span>V</span></div>
                          <div class="col border border-success-subtle rounded-3 border-3 mx-left-3 text-success sabado"><span class="fw-bold">S</span></div>
                        </div>
                        <div class="row px-2 text-center align-items-center dayHrs">
                          <div class="col tinyTxt mx-left-3 text-success domingo"><span><strong>Libre</strong></span></div>
                          <div class="col tinyTxt mx-left-3 lunes"><span>8am - 4pm</span></div>
                          <div class="col tinyTxt mx-left-3 martes"><span>8am - 4pm</span></div>
                          <div class="col tinyTxt mx-left-3 miercoles"><span>8am - 4pm</span></div>
                          <div class="col tinyTxt mx-left-3 jueves"><span>8am - 4pm</span></div>
                          <div class="col tinyTxt mx-left-3 viernes"><span>8am - 4pm</span></div>
                          <div class="col tinyTxt mx-left-3 text-success sabado"><span><strong>Libre</strong></span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row col-12 px-4">
                <div class="col-12 text-end">
                  <button type="button" id="StartUp" class="btn btn-link btnClic-Eff animate__animated"><i class="fa-solid fa-circle-right" style="font-size: 40px;"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div id="SeleccionarFechas" class="row SeccionRep col-9 text-center animate__animated" style="display: none;">
      <div class="card">
        <div class="card-body">
          <div class="row justify-content-center mb-3 Emp-info">
            <div class="col-12 mb-1">
              <h2 class="CardTtl card-title text-center">Determinar Fechas</h2>
              <span class="Emp-Pill mt-4 badge text-warning-emphasis bg-warning-subtle border border-warning-subtle rounded-pill invisible animate__animated Nombre">5445 - Jorge Antonio Tonos Carrasco</span>
            </div>
            <div class="Emp-Hr col-6 invisible animate__animated">
              <div class="row px-2 text-center align-items-center dayNames">
                <div class="domingo col border border-success-subtle rounded-3 border-3 mx-left-3 text-success fw-bold" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Libre"><span>D</span></div>
                <div class="lunes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>L</span></div>
                <div class="martes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>M</span></div>
                <div class="miercoles col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>X</span></div>
                <div class="jueves col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>J</span></div>
                <div class="viernes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>V</span></div>
                <div class="sabado col border border-success-subtle rounded-3 border-3 mx-left-3 text-success fw-bold" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Libre"><span>S</span></div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex justify-content-center align-items-center">
              <div id="calendario" class="calendario"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="DetallarDias" class="row SeccionRep col-9 text-center animate__animated" style="display: none;">
      <div class="card">
        <div class="card-body">
          <div class="row justify-content-center mb-3 Emp-info">
            <div class="col-12 mb-1">
              <h2 class="CardTtl card-title text-center">Detalle Días</h2>
              <span class="Emp-Pill mt-4 badge text-warning-emphasis bg-warning-subtle border border-warning-subtle rounded-pill invisible animate__animated Nombre">5445 - Jorge Antonio Tonos Carrasco</span>
            </div>
            <div class="Emp-Hr col-6 invisible animate__animated">
              <div class="row px-2 text-center align-items-center dayNames">
                <div class="domingo col border border-success-subtle rounded-3 border-3 mx-left-3 text-success fw-bold" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Libre"><span>D</span></div>
                <div class="lunes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>L</span></div>
                <div class="martes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>M</span></div>
                <div class="miercoles col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>X</span></div>
                <div class="jueves col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>J</span></div>
                <div class="viernes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="8am - 4pm"><span>V</span></div>
                <div class="sabado col border border-success-subtle rounded-3 border-3 mx-left-3 text-success fw-bold" role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Libre"><span>S</span></div>
              </div>
            </div>
          </div>
          <div id="DiaSelect" class="row justify-content-center">
            <div class="dayRow row mt-1 justify-content-center" style="display: none;">
              <div class="row col-5">
                <div class="dropdown mb-2">
                  <button class="selectorFechas btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    Selecciona una fecha
                  </button>
                  <ul id="listaFechasDropdown" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <!-- Las opciones del dropdown se llenarán dinámicamente con JS -->
                  </ul>
                </div>
                <p id="ponchadoDiaSelecc" class="mb-0 text-danger" role="button" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="--" style="font-size: 13px;"><i class="fa-solid fa-fingerprint mx-1"></i> 00:00 - 00:00</p>
                <p id="tipoDia" class="tinyTxt text-primary">Regular</p>
              </div>
              <div id="DetalleHoras" class="animate__animated">
                <div class="row mb-2 justify-content-between">
                  <div class="row col-4">
                    <div class="col-12 mb-1">
                      <input type="time" class="form-control input-hora-dia" id="HoraEntrada" name="HoraEntrada">
                    </div>
                    <div class="col-12">
                      <input type="time" class="form-control input-hora-dia" id="HoraSalida" name="HoraSalida">
                    </div>
                  </div>
                  <div class="col-8 text-start">
                    <textarea type="text" class="form-control" id="detDia" name="detDia" placeholder="¿Qué estabas haciendo?" rows="3"></textarea>
                  </div>
                </div>
                <div id="desgloseDia" class="row justify-content-end">
                  <div id="subTotalHRs" class="row text-end">
                    <di class="col"></di>
                    <div class="col-2 border-bottom text-start">
                      <p class="mb-0 fs-5">Sub Total</p>
                    </div>
                    <div class="col-3 border-bottom">
                      <p class="valor mb-0 text-success fs-5">0.00</p>
                    </div>
                  </div>
                  <div id="bonoDiaLibre" class="row text-end">
                    <di class="col"></di>
                    <div class="col-2 border-bottom text-start">
                      <p class="mb-0 fs-5">+ 30%</p>
                    </div>
                    <div class="col-3 border-bottom">
                      <p class="valor mb-0 text-success fs-5">0.00</p>
                    </div>
                  </div>
                  <div id="totalHRs" class="row text-end pb-2 border-bottom">
                    <di class="col"></di>
                    <div class="col-2 text-start">
                      <p class="mb-0 fs-5">Total</p>
                    </div>
                    <div class="col-3">
                      <p class="valor mb-0 text-success fs-5"><span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>0.00</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-12">
              <nav id="navDiasSelect" aria-label="Page navigation example">
                <ul class="pagination pagination-md justify-content-center mb-0">
                  <li id="diaAnterior" class="page-item" role="button"><a class="page-link"><i class="fa-solid fa-arrow-left"></i></a></li>
                  <li class="page-item" role="button"><a class="page-link"><i class="fa-solid fa-angles-down"></i></a></li>
                  <li id="diaSiguiente" class="page-item" role="button"><a class="page-link"><i class="fa-solid fa-arrow-right"></i></a></li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="ResumenReporte" class="row SeccionRep col-9 text-center animate__animated" style="display: none;">
      <div class="card">
        <div class="card-body">
          <div class="row justify-content-center mb-3">
            <div class="col-12 mb-1">
              <h2  class="CardTtl card-title text-center">Resumen</h2>
            </div>
          </div>
          <div class="row justify-content-center mb-3">
            <div class="col-12">
              <div class="card border-light">
                <div class="card-body mx-4">
                  <div class="container">
                    <div id="InfoEmp" class="row">
                      <p style="font-size: 50px;">INFOTEP</p>
                      <p class="Gerencia" style="font-size: 25px;">ONA</p>
                      <p class="Dependencia">Archivo y Corespondencia</p>
                      <p class="fst-italic">SOLICITUD PAGO DE HORAS EXTRAS</p>
                      <p class="mb-2">2024</p>
                      <div class="row">
                        <div class="col-7 text-start">
                          <p class="Nombre" style="font-size: 25px;"><strong>Fulano De Tal - 5555</strong></p>
                        </div>
                      </div>
                      <div class="row mt-1">
                        <div class="col-7 text-start">
                          <p class="text-secondary Cargo" style="font-size: 20px; font-style: italic;">Mensajero Interno</p>
                        </div>
                      </div>
                    </div>
                    <div class="row mb-2 justify-content-center">
                      <p class="mb-0">Horario</p>
                      <hr class="opacity-50 my-2" style="border-width: 3px; border-color: #023047; border-radius: 2rem">
                      <div class="row px-2 text-center align-items-center dayNames">
                        <div class="domingo col border border-success-subtle rounded-3 border-3 mx-left-3 text-success"><span>D</span></div>
                        <div class="lunes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3"><span>L</span></div>
                        <div class="martes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3"><span>M</span></div>
                        <div class="miercoles col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3"><span>X</span></div>
                        <div class="jueves col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3"><span>J</span></div>
                        <div class="viernes col text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3 mx-left-3"><span>V</span></div>
                        <div class="sabado col border border-success-subtle rounded-3 border-3 mx-left-3 text-success"><span>S</span></div>
                      </div>
                      <div class="row px-2 text-center align-items-center dayHrs">
                        <div class="col tinyTxt mx-left-3 text-success domingo"><span><strong>Libre</strong></span></div>
                        <div class="col tinyTxt mx-left-3 lunes"><span>8am - 4pm</span></div>
                        <div class="col tinyTxt mx-left-3 martes"><span>8am - 4pm</span></div>
                        <div class="col tinyTxt mx-left-3 miercoles"><span>8am - 4pm</span></div>
                        <div class="col tinyTxt mx-left-3 jueves"><span>8am - 4pm</span></div>
                        <div class="col tinyTxt mx-left-3 viernes"><span>8am - 4pm</span></div>
                        <div class="col tinyTxt mx-left-3 text-success sabado"><span><strong>Libre</strong></span></div>
                      </div>
                      <hr class="opacity-50 my-2" style="border-width: 3px; border-color: #023047; border-radius: 2rem">
                    </div>
                    <div id="RepTabl" class="row">
                      <table class="table table-striped text-start">
                        <thead>
                          <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Día</th>
                            <th scope="col">Desde</th>
                            <th scope="col">Hasta</th>
                            <th scope="col">Horas</th>
                            <th scope="col">Factor</th>
                            <th scope="col">Total</th>
                          </tr>
                        </thead>
                        <tbody class="table-group-divider">
                          <tr role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Apoyo al centro en jardinería">
                            <th scope="row">1</th>
                            <td>08/12/2023</td>
                            <td>7:54 a.m.</td>
                            <td>10:01 p.m.</td>
                            <td>14.12</td>
                            <td>1.30</td>
                            <td class="text-success"><strong>18.35</strong></td>
                          </tr>
                          <tr role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Apoyo al centro en jardinería">
                            <th scope="row">2</th>
                            <td>11/12/2023</td>
                            <td>7:44 a.m.</td>
                            <td>09:25 p.m.</td>
                            <td>5.68</td>
                            <td>1.00</td>
                            <td class="text-success"><strong>5.68</strong></td>
                          </tr>
                          <tr role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Apoyo al centro en jardinería">
                            <th scope="row">3</th>
                            <td>12/12/2023</td>
                            <td>1:56 a.m.</td>
                            <td>06:44 p.m.</td>
                            <td>8.80</td>
                            <td>1.00</td>
                            <td class="text-success"><strong>8.80</strong></td>
                          </tr>
                          <tr style="font-weight: bold; font-size: 20px;">
                            <td class="text-success text-end" colspan="6">Total</td>
                            <td class="text-success">32.83</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div id="RepDet" class="row justify-content-end">
                      <div class="col-5 text-start col-5 text-start py-2 rounded-4 border border-5 border-success-subtle">
                        <div class="row">
                          <div class="col text-end">
                            <p>Sueldo</p>
                          </div>
                          <div class="col">
                            <p class="Sueldo">RD$23,568.00</p>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col text-end">
                            <p>Tasa x Hora</p>
                          </div>
                          <div class="col">
                            <p class="Tasa_Hora">RD$135.94</p>
                          </div>
                        </div>
                        <div class="row fw-bold text-success">
                          <div class="col text-end">
                            <p>Pago Total</p>
                          </div>
                          <div class="col">
                            <p class="Pago_Total">RD$4,660.54 </p>
                          </div>
                        </div>
                        <div class="row justify-content-center">
                          <div class="Porciento_Sueldo col-5">
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis" >18.93% del sueldo</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 text-end">
                        <button type="button" id="SendRep" class="btn btn-link btnClic-Eff animate__animated px-0"><i class="fa-solid fa-circle-right" style="font-size: 40px;"></i></button>
                      </div>
                    </div>
                    <div class="row text-center justify-content-center mt-1">
                      <div class="col-5">
                        <hr>
                        <button class="col-3 btn btn-sm btn-primary"><i class="fa-solid fa-print"></i></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="Confirmación" class="row SeccionRep col-9 text-center animate__animated" style="display: none;">
      <div class="card">
        <div class="card-body">
          <div class="row justify-content-center">
            <div class="col-12 pt-2">
              <h2 class="CardTtl card-title text-center" style="font-size: 50px;">¡Listo!</h2>
            </div>
            <div class="col-12 pt-2">
              <img class="w-25" src="img/checkmark-running.svg">
            </div>
            <div class="col-12 pt-2" style="font-size: 25px;">
              <p>Tu reporte ha sido enviado a revisión satisfactoriamente. ¡Gracias!</p>
              <button id="ExitHE" class="btn btn-link">Salir</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer-->
<footer style="padding-top: 10rem; padding-bottom: 3rem;">
  <div class="container"><p class="m-0 text-center">Copyright &copy; INFOTEP 2024</p></div>
</footer>
<!-- jQuery -->
<script src="../Librerias/jquery v3.7.1/jquery-3.7.1.js"></script>
<!-- Bootstrap core JS-->
<script src="../Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/js/bootstrap.bundle.min.js"></script>
<!-- Core theme JS-->
<script src="../Librerias/Select2/select2-4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://kit.fontawesome.com/7406913e7b.js" crossorigin="anonymous"></script>
<script src="../Librerias/Flatpickr/flatpickr-master/src/flatpickr.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script src="js/admReps.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>
