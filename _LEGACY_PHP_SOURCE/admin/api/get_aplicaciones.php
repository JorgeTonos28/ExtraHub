<?php
include_once '../../includes/pdo_db_connect.php';

$query = "SELECT id_app, nombre_app FROM aplicaciones";
$stmt = $pdo->prepare($query);
$stmt->execute();
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($apps);
?>
