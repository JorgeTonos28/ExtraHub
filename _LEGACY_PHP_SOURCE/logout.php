<?php
session_start(); // Iniciar la sesión

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio o de inicio de sesión
header("Location: login.php");
exit;
?>
