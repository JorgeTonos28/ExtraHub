<?php
include_once '../../includes/db_connect.php';

$query = "SELECT id_departamento, tipo_id, nombre, dependencia_id, gerencia_id, cod_archivo FROM departamentos";
$result = mysqli_query($conn, $query);
$departamentos = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($departamentos);