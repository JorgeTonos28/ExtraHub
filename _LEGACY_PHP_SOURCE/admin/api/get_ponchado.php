<?php
require '../vendor/autoload.php';

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

// Datos de conexión a la base de datos
$host = 'localhost';
$dbname = 'infotepadm_MainDB';
$user = 'infotepadm_infotepadm';
$password = '5_*ucBNZJ8wK';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $personalRange = 'Personal!H1';
    $personalResponse = $service->spreadsheets_values->get($spreadsheetId, $personalRange);
    $personalValues = $personalResponse->getValues();
    $ultAct = isset($personalValues[0][0]) ? $personalValues[0][0] : 'No disponible';

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT codigo, fecha, entrada, salida, excepcion FROM Ponchado_Sheets");
    
    $ponchado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["ponchado" => $ponchado, "ultAct" => $ultAct]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
