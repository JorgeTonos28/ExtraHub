<?php
session_start();
include_once '../../includes/pdo_db_connect.php'; // Asegúrate de ajustar esta ruta

header('Content-Type: application/json');

// Recibe los datos enviados desde el cliente
$fecha = $_POST['fecha'] ?? '';
$codigoEmpleado = $_SESSION['codigoEmpleado'] ?? null;
if (empty($fecha) || empty($codigoEmpleado)) {
    echo json_encode(['valido' => false, 'mensaje' => 'La fecha o el código del empleado están vacíos. ']);
    exit;
}

// Prepara la consulta SQL
$query = "SELECT entrada, salida, excepcion FROM Ponchado_Sheets WHERE codigo = :codigo AND fecha = :fecha";

// Prepara y ejecuta la consulta
$stmt = $pdo->prepare($query);
$stmt->execute([':codigo' => $codigoEmpleado, ':fecha' => $fecha]);
$row = $stmt->fetch();

if ($row) {
    $row['excepcion'] = strtoupper($row['excepcion']); // Convierte a mayúsculas
    $mensaje = '';
    $valido = false;

    if (is_null($row['entrada']) && is_null($row['salida'])) {
        $mensaje = 'Faltan ambos ponchados para este día.';
        if ($row['excepcion'] === "OMISION DE PONCHADO" || $row['excepcion'] === "SERVICIO EXTERNO") {
            $mensaje .= ' Sin embargo, tiene una excepción aprobada y válida.';
            $valido = true;
        } else {
            $mensaje .= ' Además, no tiene ninguna excepción aprobada en el sistema.';
        }
    } elseif (is_null($row['entrada']) || is_null($row['salida'])) {
        $ponchadoFaltante = is_null($row['entrada']) ? 'entrada' : 'salida';
        $mensaje = "Falta el ponchado de $ponchadoFaltante para este día.";
        if (!empty($row['excepcion']) && $row['excepcion'] !== "--") {
            $mensaje .= ' Pero tiene una excepción aprobada y válida.';
            $valido = true;
        } else {
            $mensaje .= ' Además, no tiene una excepción aprobada y válida.';
        }
    } else {
        $mensaje = 'Tiene ambos ponchados para este día.';
        $valido = true;
    }

    echo json_encode(['valido' => $valido, 'mensaje' => $mensaje]);
} else {
    echo json_encode(['valido' => false, 'mensaje' => 'No se encontraron datos para el día seleccionado: ']);
}
?>