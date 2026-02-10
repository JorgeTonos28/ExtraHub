<?php
include_once '../../includes/db_connect.php';
    
    header('Content-Type: application/json; charset=utf-8');
    
    $termino = $_GET['termino'] ?? '';
    $campo = $_GET['campo'] ?? 'nombre'; // 'codigo', 'nombre' o 'cedula'
    
    $query = "";
    if ($campo === 'codigo') {
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE codigo LIKE ?";
    } elseif ($campo === 'nombre') {
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE nombre LIKE ?";
    } else { // 'cedula'
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE cedula LIKE ?";
    }
    
    $stmt = $conn->prepare($query);
    $likeTermino = '%' . $termino . '%';
    $stmt->bind_param("s", $likeTermino);
    $stmt->execute();
    $result = $stmt->get_result();
    $coincidencias = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($coincidencias);   

