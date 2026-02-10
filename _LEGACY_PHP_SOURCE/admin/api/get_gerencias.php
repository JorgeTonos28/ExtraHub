<?php
include_once '../../includes/db_connect.php';

$query = "SELECT id_gerencia, nombre FROM nombres_gerencias";
$result = mysqli_query($conn, $query);
$gerencias = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($gerencias);