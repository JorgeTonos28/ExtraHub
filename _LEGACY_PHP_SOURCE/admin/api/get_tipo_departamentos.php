<?php
include_once '../../includes/pdo_db_connect.php'; // Ajusta la ruta según sea necesario

header('Content-Type: application/json');

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id_tipo, nombre FROM tipos_departamentos");
    $stmt->execute();
    $tiposDepartamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tiposDepartamentos);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollback();
    echo json_encode(["error" => "Error al recuperar tipos de departamentos: " . $e->getMessage()]);
}
?>
