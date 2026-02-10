<?php
$servername = "localhost";
$username = "infotepadm_infotepadm";
$password = "5_*ucBNZJ8wK";
$dbname = "infotepadm_MainDB";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Chequear conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
