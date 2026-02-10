<?php
include_once '../../includes/db_connect.php'; // Ajusta la ruta según sea necesario

$query = "SELECT id_rol, nombre_rol FROM roles";
$result = mysqli_query($conn, $query); // Asumiendo que $conn es tu conexión a la base de datos
$roles = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($roles);
