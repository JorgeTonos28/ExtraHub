<?php

session_start(); // Asegura que la sesión esté iniciada
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fechas']) && is_array($_POST['fechas'])) {
        $codigo_usuario = $_SESSION['codigoEmpleado'] ?? null; // Asumir que el código de usuario está almacenado en la sesión

        if ($codigo_usuario) {
            // Almacenar fechas en la sesión para el usuario actual
            $_SESSION['fechas_validadas'][$codigo_usuario] = $_POST['fechas'];
            if (!isset($_SESSION['DiaActivo'])) {
            	$_SESSION['DiaActivo'] = 1;
            }
            echo json_encode(['success' => true]);

            // Depurar las fechas almacenadas en el log
            // error_log("Fechas almacenadas para el usuario {$codigo_usuario}: " . implode(', ', $_SESSION['fechas_validadas'][$codigo_usuario]));
        } else {
            echo json_encode(['success' => false, 'message' => 'Código de usuario no disponible en la sesión.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se recibieron fechas válidas o el formato no es correcto.']);
    }
} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>