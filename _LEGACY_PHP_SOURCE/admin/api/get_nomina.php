<?php
require '../vendor/autoload.php';

// Datos de conexión a la base de datos
$host = 'localhost';
$dbname = 'infotepadm_MainDB';
$user = 'infotepadm_infotepadm';
$password = '5_*ucBNZJ8wK';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT codigo, nombre, cedula, departamento, cargo, salario_mensual, compensacion_vehiculo FROM nomina");
    
    $nominas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($nominas);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
