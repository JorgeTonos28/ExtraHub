<?php
require_once '../../vendor/autoload.php';
include_once '../../includes/pdo_db_connect.php'; // Ajusta la ruta según sea necesario

$client = new Google_Client();
$client->setApplicationName('Refrigerios');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);

// Aquí asumimos que ya tienes estas variables definidas con tus credenciales y refresh_token
$clientId = '1051028593338-fcj9pm2r6pedsrek04upa5e3cvojormp.apps.googleusercontent.com';
$clientSecret = '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';
$refreshToken = '1//04TphBbWMJVUoCgYIARAAGAQSNwF-L9IrQzJHNMsUrtnJ54uNkR6zY3zfzASqgqLApLij-FH7tMtbz0wlHIh5hCFzPR0vLfux3wM';

function getNewAccessToken($clientId, $clientSecret, $refreshToken) {
    $url = 'https://oauth2.googleapis.com/token';
    $params = array(
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "refresh_token" => $refreshToken,
        "grant_type" => "refresh_token",
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    return $data->access_token;
}

// Obtener un nuevo access_token
$newAccessToken = getNewAccessToken($clientId, $clientSecret, $refreshToken);
$client->setAccessToken($newAccessToken);

$service = new Google_Service_Sheets($client);
$spreadsheetId = '1N4vdo0txKj5Sm4YX1jUd-YpIFevyNyWRn2cmJrZkPSE';

// Intenta obtener el rango completo de la hoja "Ponchado"
try {

    $personalRange = 'Personal!H1';
    $personalResponse = $service->spreadsheets_values->get($spreadsheetId, $personalRange);
    $personalValues = $personalResponse->getValues();

    // Asume que la celda H1 contiene un valor simple y no está vacía
    $ultimaAct = $personalValues[0][0];

    // Verificar la última fila con datos en la columna "I"
    // $response = $service->spreadsheets_values->get($spreadsheetId, 'Ponchado!I:I');
    // $values = $response->getValues();
    // $lastRow = count($values);

    // if ($lastRow === 1) {
    //     throw new Exception("No se encontraron datos en la hoja 'Ponchado'.");
    // }

    $range = 'Ponchado!A2:I'; // Considerar ajustar dinámicamente si es necesario
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    $codigoEmpleado = null;
    $Found = 0;

    foreach ($values as $row) {
        $indicador = substr($row[0], 0, 4);
        if (is_numeric($indicador) && $Found !== 0.5) {
            $stmt = $pdo->prepare("SELECT codigo FROM usuarios WHERE codigo = ?");
            $stmt->execute([$indicador]);
            if ($stmt->fetch()) {
                $codigoEmpleado = $indicador;
                $Found = 0.5;
            }
        } elseif ($row[0] === "--" && $Found === 0.5) {
            // Procesa los registros para este empleado
            // Para $entrada
            if (strpos($row[3], ' ') !== false && strlen($row[3]) > 5) {
                // Si hay un espacio y la longitud sugiere fecha y hora, extrae solo la hora
                $entradaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $row[3]);
                $entrada = $entradaDateTime->format('H:i:s');
            } else {
                // Si no, asume que es solo hora o "-", maneja como antes
                $entrada = $row[3] === "-" ? null : $row[3];
            }

            // Repite la lógica para $salida
            if (strpos($row[4], ' ') !== false && strlen($row[4]) > 5) {
                $salidaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $row[4]);
                $salida = $salidaDateTime->format('H:i:s');
            } else {
                $salida = $row[4] === "-" ? null : $row[4];
            }
            $fecha = DateTime::createFromFormat('d/m/Y', $row[2])->format('Y-m-d');

            // Log para fines de depuración
            // error_log($codigoEmpleado . " - " . $row[2] . " - " . $entrada . " - " . $salida . " - " . $row[8]);

            $stmt = $pdo->prepare("INSERT INTO Ponchado_Sheets (codigo, fecha, entrada, salida, excepcion) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE entrada = ?, salida = ?, excepcion = ?");
            $stmt->execute([$codigoEmpleado, $fecha, $entrada, $salida, $row[8], $entrada, $salida, $row[8]]);
        } elseif ($row[0] === "") {
            $Found = 0; // Resetea para el próximo empleado
        }
    }

    echo json_encode(["success" => "Datos procesados e insertados correctamente.", "ultimaAct" => $ultimaAct]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}


?>