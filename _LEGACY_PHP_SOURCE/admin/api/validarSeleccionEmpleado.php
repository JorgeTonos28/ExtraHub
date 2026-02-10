<?php
session_start();
include '../../includes/pdo_db_connect.php'; // Asegúrate de incluir tu script de conexión a la base de datos aquí

// Asumiendo que ya has validado que el usuario está autenticado y has almacenado su departamento_id y user_role_id en $_SESSION
$usuarioDepartamentoId = $_SESSION['departamento_id'];
$usuarioRolId = $_SESSION['user_role_id'];
$empleadoDepartamentoId = $_GET['depID_Selected'];
$empleadoNivelCorporativo = $_GET['nivelCorporativo'];
$usuarioGerenciaId = $_SESSION['gerencia_id'];
$empleadoGerenciaId = $_GET['gerencia_id'];
$codigoEmpleado = $_GET['codigoEmpleado'];
// Inicializa la respuesta
$respuesta = [
    'esValido' => false,
    'mensaje' => 'No tienes permiso para seleccionar este empleado.'
];

// Permitir la selección si el usuario es administrador regional (1) o general (5),
// o si es encargado (2) o ayudante (3) y el empleado está en su departamento y es un personal de apoyo
if ($empleadoNivelCorporativo == 5) {  
    if ($usuarioGerenciaId == $empleadoGerenciaId) {
        if (($usuarioRolId == 1 || $usuarioRolId == 5) || (($usuarioRolId == 2 || $usuarioRolId == 3) && $usuarioDepartamentoId == $empleadoDepartamentoId)) {
            
            $respuesta['esValido'] = true;
            $respuesta['mensaje'] = 'Selección válida.';
            $_SESSION['codigoEmpleado'] = str_pad($codigoEmpleado, 10, "0", STR_PAD_LEFT); // Añade ceros a la izquierda hasta completar 10 dígitos, igual que en la BD;
        } else {
            $respuesta['esValido'] = false;
            $respuesta['mensaje'] = 'Departamento';
        }
    } else {
        $respuesta['mensaje'] = 'Gerencia';
    }
} else {
    $respuesta['esValido'] = false;
    $respuesta['mensaje'] = 'Nivel';
}

// Devolver resultado
echo json_encode($respuesta);
?>
