<?php
//Desactivar la visualización de errores en producción
ini_set('display_errors', '0'); // '0' o 'off' para desactivar
error_reporting(0); // Desactiva la notificación de todos los errores
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include '../includes/db_connect.php'; // Incluye el archivo de conexión

header('Content-Type: application/json'); // Establece el tipo de contenido como JSON

try {
    // Preparar la sentencia SQL
    $query = "SELECT codigo, nombre FROM empleados"; // Cambia los campos según necesites
    $stmt = $conn->prepare($query);

    // Ejecutar la sentencia preparada
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();

    $employees = array();
    if ($result->num_rows > 0) {
        // Salida de cada fila
        while($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        // // Imprimir para depuración
        // print_r($employees);
        echo json_encode($employees);
        if (json_last_error() != JSON_ERROR_NONE) {
            echo 'Error en JSON: ' . json_last_error_msg();
        }
    } else {
        echo json_encode(array('error' => 'No se encontraron empleados.'));
    }
} catch (Exception $e) {
    // En caso de error, devuelve un JSON con información del error
    echo json_encode(array('error' => $e->getMessage()));
}

// Cerrar la sentencia preparada
$stmt->close();

// Cerrar la conexión a la base de datos
$conn->close();
?>
