<?php
session_start();
include_once  '../../includes/pdo_db_connect.php'; // Asegúrate de ajustar esta ruta
header('Content-Type: application/json');

$codigoEmpleado = $_SESSION['codigoEmpleado'] ?? null;
$fechasValidadas = $_SESSION['fechas_validadas'][$codigoEmpleado] ?? [];
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'generar_reporte':

        if (empty($codigoEmpleado) || empty($fechasValidadas)) {
            echo json_encode(['success' => false, 'message' => 'Código de empleado no especificado.']);
            exit;
        }

        // Ajustar el código del empleado para la consulta en nómina
        $codigoEmpleadoCorto = ltrim($codigoEmpleado, '0');

        $placeholders = implode(',', array_fill(0, count($fechasValidadas), '?'));
        $sql = "SELECT * FROM reportes_horas_extras 
                WHERE codigo_usuario = ? 
                AND (
                    (fecha IN ($placeholders) AND total_decimal > 0)
                    OR 
                    estado IN ('creado', 'revisar', 'pendiente', 'aprobado')
                )
                ORDER BY fecha ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$codigoEmpleado], $fechasValidadas));

        $reportes = $stmt->fetchAll();

        if ($reportes) {
            $sqlSueldo = "SELECT salario_mensual FROM nomina WHERE codigo = ?";
            $stmtSueldo = $pdo->prepare($sqlSueldo);
            $stmtSueldo->execute([$codigoEmpleadoCorto]);
            $salarioMensual = $stmtSueldo->fetchColumn();

            if ($salarioMensual) {
                $tasaHora = ($salarioMensual / 27.67) / 8;
                $totalHoras = array_sum(array_column($reportes, 'total_decimal'));
                $pagoTotal = $totalHoras * $tasaHora;
                $porcentajeDelSueldo = ($pagoTotal / $salarioMensual) * 100;

                echo json_encode([
                    'success' => true,
                    'totalHoras' => $totalHoras,
                    'sueldo' => $salarioMensual,
                    'tasaHora' => $tasaHora,
                    'pagoTotal' => $pagoTotal,
                    'porcentajeDelSueldo' => $porcentajeDelSueldo,
                    'reportes' => $reportes
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el salario mensual del empleado.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Ninguno de los reportes nuevos están completos o no tiene reportes creados pendientes de pago para este período.']);
        }

        break;
    
    case 'actualizar_estado':

        if (empty($codigoEmpleado) || empty($fechasValidadas)) {
            echo json_encode(['success' => false, 'message' => 'No hay fechas validadas o código de empleado.']);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($fechasValidadas), '?'));
        $sql = "SELECT id_reporte, fecha, descripcion FROM reportes_horas_extras WHERE codigo_usuario = ? AND fecha IN ($placeholders) AND total_decimal > 0 AND estado = 'incompleto'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$codigoEmpleado], $fechasValidadas));
        $reportes = $stmt->fetchAll();

        $reportesConProblemas = array_filter($reportes, function($reporte) {
            return strlen(trim($reporte['descripcion'])) < 20; // Descripción debe tener al menos 50 caracteres
        });

        if (count($reportesConProblemas) > 0) {
            $fechasProblema = array_map(function($reporte) {
                return $reporte['fecha']; // Devolver la fecha del reporte en lugar del id
            }, $reportesConProblemas);
            echo json_encode(['success' => false, 'message' => 'Algunos reportes tienen descripciones insuficientes.', 'fechas' => $fechasProblema]);
            exit;
        }

        // Filtrar y actualizar solo los que no están ya en estado 'creado'
        $idsParaActualizar = array_column(array_filter($reportes, function($reporte) {
            return $reporte['estado'] !== 'creado'; // Solo si no están ya creados
        }), 'id_reporte');

        if (count($idsParaActualizar) > 0) {
            $placeholders = implode(',', array_fill(0, count($idsParaActualizar), '?'));
            $sqlUpdate = "UPDATE reportes_horas_extras SET estado = 'creado' WHERE id_reporte IN ($placeholders)";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($idsParaActualizar);
            echo json_encode(['success' => true, 'message' => 'Estados nuevos actualizados correctamente.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'No hay reportes que requieran actualización.']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
}
?>

