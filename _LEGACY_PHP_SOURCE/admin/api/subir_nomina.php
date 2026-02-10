<?php

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Datos de conexión a la base de datos
$host = 'localhost';
$dbname = 'infotepadm_MainDB';
$user = 'infotepadm_infotepadm';
$password = '5_*ucBNZJ8wK';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivoNomina'])) {
    $archivoExcel = $_FILES['archivoNomina']['tmp_name']; // Ruta temporal al archivo subido
    // Asume que recibes los datos como un array de arrays desde el cliente
    // $datosParaInsertar = json_decode($_POST['datos'], true);
    try {

        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Aquí asumiremos que ya tienes el archivo Excel subido al servidor
        $archivoExcel = $_FILES['archivoNomina']['tmp_name']; // Ruta temporal al archivo subido
        $reader = new Xlsx();
        $spreadsheet = $reader->load($archivoExcel);

        // Prepara la sentencia de inserción
        $stmt = $pdo->prepare("INSERT INTO nomina (codigo, nombre, cedula, departamento, cargo, salario_mensual, compensacion_vehiculo) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Vaciar la tabla antes de insertar los nuevos datos
        $pdo->exec("TRUNCATE TABLE nomina");

        $sumatoriaSalarioMensual = 0;
        $datosValidos = true;
        $errorDetalle = ""; // Inicializa una variable para almacenar el mensaje de error

        // Iterar sobre todas las hojas del archivo Excel
        foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
            $sheet = $spreadsheet->getSheet($sheetIndex);

            // Verificar el indicador principal en la celda A1
            if ($sheet->getCell('A1')->getValue() != "INFOTEP") {
                $datosValidos = false;
                $errorDetalle = "La hoja [" . $sheetName . "] no contiene 'INFOTEP' en la celda A1.";
                break;
            }
            
            // Verificar los encabezados en la fila 3
            $headers = ['A3' => 'Código ', 'B3' => 'Nombre', 'C3' => 'Cédula', 'D3' => 'Departamento', 'E3' => 'Cargo o Posición', 'F3' => 'Salario Quincenal', 'G3' => 'Salario Mensual', 'H3' => 'COMPENSACION POR USO DE VEHICULOS'];
            foreach ($headers as $key => $value) {
                if ($sheet->getCell($key)->getValue() != $value) {
                    $datosValidos = false;
                    $errorDetalle = "La hoja [" . $sheetName . "] no contiene el encabezado esperado en [" . $key . "]. Se esperaba [" . $value . "].";
                    break 2; // Rompe ambos bucles
                }
            }

            if ($datosValidos) {
                foreach ($sheet->getRowIterator(4) as $row) { // Comenzar desde la fila 4 para saltar los encabezados
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(FALSE); // Loop all cells, even if it is not set

                    $row_data = [];
                    foreach ($cellIterator as $cell) {
                        // Obtén el valor calculado si es una fórmula
                        if ($cell->isFormula()) {
                            $calculatedValue = $sheet->getCell($cell->getCoordinate())->getCalculatedValue();
                            $row_data[] = $calculatedValue;
                        } else {
                            $row_data[] = $cell->getValue();
                        }
                    }
                    
                    // Comprobar si la fila contiene datos de empleado (ignorar filas de totales y vacías)
                    if (!empty($row_data[0])) {
                        // Eliminar la columna de Salario Quincenal (asumiendo que es la columna F)
                        array_splice($row_data, 5, 1);

                        // Sumar el salario mensual
                        $sumatoriaSalarioMensual += $row_data[5];

                        // Preparar los datos para la inserción
                        $stmt->execute([
                            $row_data[0], // código
                            $row_data[1], // nombre
                            $row_data[2], // cédula
                            $row_data[3], // departamento
                            $row_data[4], // cargo
                            $row_data[5], // salario mensual
                            $row_data[6], // compensación por uso de vehículo
                        ]);
                    }
                }
            }
        }

        // Si los datos no son válidos, envía un mensaje de error
        if (!$datosValidos) {
            echo json_encode(['error' => "El archivo no tiene el formato correcto " . $errorDetalle]);
            exit;  // Asegúrate de terminar la ejecución del script aquí.
        }

        // Envía la sumatoria para confirmación del usuario
        echo json_encode(['sumatoriaSalarioMensual' => $sumatoriaSalarioMensual]);
        
    } catch (PDOException $e) {
        echo json_encode(['Error en la conexión a la base de datos: ' => $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['Error : ' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => "No se ha subido ningún archivo."]);
}
?>