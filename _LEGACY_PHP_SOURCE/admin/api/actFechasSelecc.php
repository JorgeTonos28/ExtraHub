<?php
session_start(); // Asegura que la sesión esté iniciada
header('Content-Type: application/json');

$codigo_usuario = $_SESSION['codigoEmpleado'] ?? null; // Asumir que el código de usuario está almacenado en la sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fechas']) && is_array($_POST['fechas'])) {
        if ($codigo_usuario) {
            // Almacenar fechas en la sesión para el usuario actual
            $_SESSION['fechas_validadas'][$codigo_usuario] = $_POST['fechas'];

            // Si aún no se ha establecido, inicializar DiaActivo
            if ($_SESSION['DiaActivo'][$codigo_usuario] == 0) {
                $_SESSION['DiaActivo'][$codigo_usuario] = 1; // Comienza por el primer día activo
            }

            echo json_encode(['success' => true, 'DiaActivo' => $_SESSION['DiaActivo'][$codigo_usuario]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Código de usuario no disponible en la sesión.']);
        }
    } elseif (isset($_POST['diaActivo'])) {
        // Actualizar el día activo si se envía el índice del día activo
        if ($codigo_usuario && is_numeric($_POST['diaActivo'])) {
            $_SESSION['DiaActivo'][$codigo_usuario] = (int) $_POST['diaActivo'];

            echo json_encode(['success' => true, 'DiaActivo' => $_SESSION['DiaActivo'][$codigo_usuario]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos proporcionados son inválidos o el código de usuario no está en sesión.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se recibieron datos válidos para procesar.']);
    }
} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
