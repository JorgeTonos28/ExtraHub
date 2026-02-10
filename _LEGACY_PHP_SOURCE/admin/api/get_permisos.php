<?php
include_once '../../includes/db_connect.php';

// Obtener el ID del rol desde la petición, si está presente
$idRol = isset($_GET['idRol']) ? intval($_GET['idRol']) : null;

$permisos = array();
if ($idRol) {
    $query = "SELECT p.id_permiso, p.nombre_permiso, p.descripcion 
              FROM permisos p
              INNER JOIN rol_permisos rp ON p.id_permiso = rp.id_permiso
              WHERE rp.id_rol = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idRol);
    $stmt->execute();
    $result = $stmt->get_result();
    $permisos = $result->fetch_all(MYSQLI_ASSOC);
}

echo json_encode($permisos);
?>
