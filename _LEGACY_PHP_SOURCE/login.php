<?php
session_start();

if (isset($_SESSION['user_id'])) {
  
  if (isset($_SESSION['last_visited'])) {
    $redirect_url = $_SESSION['last_visited'];
    header("Location: " . $redirect_url);
    exit;
  }else{
     header('Location: index.php');
     exit;
  }
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
      <meta name="description" content="" />
      <meta name="author" content="" />
      <title>Ingresar</title>
      <!-- Favicon-->
      <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
  <!-- Bootstrap CSS -->
  <link href="/Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- Core theme CSS-->
      <!-- <link href="css/loginstyle.css" rel="stylesheet" /> -->
  </head>
  <style>
    .gradient-custom {
    /* fallback for old browsers */
    background: #6a11cb;
    bac

    /* Chrome 10-25, Safari 5.1-6 */
    background: -webkit-linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1));

    /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    background: linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1))
    }
  </style>
  <body>
    
    <!-- Modal-->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">¡Oops!</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary">OK</button>
          </div>
        </div>
      </div>
    </div>

    <section class="gradient-custom">
      <div class="container py-5">
        <div class="row d-flex justify-content-center align-items-center">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card bg-dark text-white" style="border-radius: 1rem;">
              <div class="card-body p-5 text-center">

                <div class="mb-md-5 mt-md-4 pb-5">

                  <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
                  <p class="text-white-50 mb-5">Por favor introduce tu usuario y contraseña</p>
                  <form id="loginForm">
                    <div class="form-outline form-white mb-4">
                      <input type="email" name="correo" id="correo" class="form-control form-control-lg" required />
                      <label class="form-label" for="correo">Correo</label>
                    </div>

                    <div class="form-outline form-white mb-4">
                      <input type="password" name="password" id="password" class="form-control form-control-lg" required />
                      <label class="form-label" for="password">Contraseña</label>
                    </div>

                    <p class="small mb-5 pb-lg-2"><a class="text-white-50" href="#!">Olvidaste la contraseña?</a></p>

                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Ingresar</button>
                  </form>
                  <div class="d-flex justify-content-center text-center mt-4 pt-1">
                    <a href="#!" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#!" class="text-white"><i class="fab fa-twitter fa-lg mx-4 px-2"></i></a>
                    <a href="#!" class="text-white"><i class="fab fa-google fa-lg"></i></a>
                  </div>

                </div>

                <div>
                  <p class="mb-0">No tienes una cuenta aún? <a href="#!" class="text-white-50 fw-bold">Solicita una</a>
                  </p>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer-->
      <footer class="py-5">
          <div class="container"><p class="m-0 text-center text-white">Copyright &copy; INFOTEP 2024</p></div>
      </footer>
    </section>
    <!-- jQuery -->
    <script src="/Librerias/jquery v3.7.1/jquery-3.7.1.js"></script>
        <!-- Bootstrap core JS-->
    <script src="/Librerias/Bootstrap v5.3/bootstrap-5.3.2-dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
      <script src="js/scripts.js"></script>
  </body>
</html>