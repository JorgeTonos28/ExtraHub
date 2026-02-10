<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../../includes/pdo_db_connect.php'; // Ajusta la ruta según sea necesario

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización de los datos recibidos del formulario
    $tipoDepartamento = filter_input(INPUT_POST, 'tipoDepartamento', FILTER_SANITIZE_NUMBER_INT);
    $nombreDepartamento = isset($_POST['nombreDepartamento']) ? htmlspecialchars($_POST['nombreDepartamento'], ENT_QUOTES, 'UTF-8') : '';
    $dependencia = !empty($_POST['dependencia']) ? filter_input(INPUT_POST, 'dependencia', FILTER_SANITIZE_NUMBER_INT) : null;
    $gerencia = filter_input(INPUT_POST, 'gerencia', FILTER_SANITIZE_NUMBER_INT);
    $codigoArchivo = filter_var($_POST['codigoArchivo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    try {
        $pdo->beginTransaction();


        // Validación de código de archivo
        $stmt = $pdo->prepare("SELECT d.cod_archivo, d.nombre AS nombre_departamento, t.nombre AS nombre_tipo FROM departamentos d INNER JOIN tipos_departamentos t ON d.tipo_id = t.id_tipo WHERE d.cod_archivo = ?");
        $stmt->execute([$codigoArchivo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $nombreDepartamentoExistente = $result['nombre_departamento'];
            $nombreTipoDepartamento = $result['nombre_tipo'];
            echo json_encode(["error" => "Este código de archivo ya está asignado a: $nombreTipoDepartamento de $nombreDepartamentoExistente"]);
            exit;
        }

        // Preparar la consulta SQL para insertar el nuevo departamento
        $stmt = $pdo->prepare("INSERT INTO departamentos (tipo_id, nombre, dependencia_id, gerencia_id, cod_archivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$tipoDepartamento, $nombreDepartamento, $dependencia, $gerencia, $codigoArchivo]);

        // Confirmar la transacción
        $pdo->commit();

        echo json_encode(["success" => "Departamento creado exitosamente"]);
    } catch (PDOException $e) {
        // En caso de error, revertir la transacción
        $pdo->rollback();
        echo json_encode(["error" => "Error al crear departamento: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Método no permitido"]);
}
?>
