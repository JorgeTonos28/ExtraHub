<?php

session_start();
include_once '../../includes/pdo_db_connect.php'; // Asegúrate de ajustar esta ruta
header('Content-Type: application/json');

$codigoEmpleado = $_SESSION['codigoEmpleado'] ?? null;
$accion = $_POST['accion'] ?? '';

if (empty($codigoEmpleado)) {
    echo json_encode(['success' => false, 'message' => 'Código de empleado no especificado.']);
    exit;
}

switch ($accion) {
    case 'iniciar_reporte':
        function crearReporte($codigo_usuario, $fecha) {
            global $pdo;  // Utiliza la conexión PDO que estableciste en tu archivo de conexión
            $estado = 'incompleto';  // Estado inicial para el nuevo reporte

            // Verifica que el código del usuario y la fecha sean del formato esperado
            if (!preg_match('/^\d+$/', $codigo_usuario)) {
                return ['success' => false, 'message' => 'Código de usuario inválido'];
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {  // Verifica que la fecha tenga el formato YYYY-MM-DD
                return ['success' => false, 'message' => 'Formato de fecha inválido'];
            }

            // Verifica si ya existe un reporte para este usuario y fecha
            $sqlCheck = "SELECT COUNT(*) FROM reportes_horas_extras WHERE codigo_usuario = ? AND fecha = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$codigo_usuario, $fecha]);
            $exists = $stmtCheck->fetchColumn() > 0;

            if ($exists) {
                return ['success' => true, 'message' => 'Ya existe un reporte para este usuario y fecha'];
            }

            $sql = "INSERT INTO reportes_horas_extras (codigo_usuario, fecha, estado) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([$codigo_usuario, $fecha, $estado]);
                return ['success' => true, 'message' => 'Reporte creado exitosamente'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Error al crear el reporte: ' . $e->getMessage()];
            }
        }

        // Recibir parámetros de AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo_usuario = $codigoEmpleado;
            $fecha = $_POST['fecha'] ?? null;

            if ($codigo_usuario && $fecha) {
                $response = crearReporte($codigo_usuario, $fecha);
                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            }
        }
        break;
    case 'obtener_ponchado':
        // Recibe los datos enviados desde el cliente
        $fecha = $_POST['fecha'] ?? '';
        if (empty($fecha) || empty($codigoEmpleado)) {
            echo json_encode(['valido' => false, 'mensaje' => 'La fecha o el código del empleado están vacíos. ' . $codigoEmpleado]);
            exit;
        }

        // Prepara la consulta SQL
        $query = "SELECT entrada, salida, excepcion FROM Ponchado_Sheets WHERE codigo = :codigo AND fecha = :fecha";

        // Prepara y ejecuta la consulta
        $stmt = $pdo->prepare($query);
        $stmt->execute([':codigo' => $codigoEmpleado, ':fecha' => $fecha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['excepcion'] = strtoupper($row['excepcion']); // Convierte a mayúsculas
            $mensaje = '';
            $valido = false;

            if (is_null($row['entrada']) && is_null($row['salida'])) {
                $mensaje = 'Faltan ambos ponchados para este día.';
                if ($row['excepcion'] === "OMISION DE PONCHADO" || $row['excepcion'] === "SERVICIO EXTERNO") {
                    $mensaje .= $row;
                    $valido = true;
                } else {
                    $mensaje .= ' Además, no tiene ninguna excepción aprobada en el sistema.';
                }
            } elseif (is_null($row['entrada']) || is_null($row['salida'])) {
                $ponchadoFaltante = is_null($row['entrada']) ? 'entrada' : 'salida';
                $mensaje = "Falta el ponchado de $ponchadoFaltante para este día.";
                if (!empty($row['excepcion']) && $row['excepcion'] !== "--") {
                    $mensaje .= $row;
                    $valido = true;
                } else {
                    $mensaje .= ' Además, no tiene una excepción aprobada y válida.';
                }
            } else {
                if ($row['entrada'] == $row['salida']) {
                    $mensaje = "Solo ponchó una sola vez en este día.";
                    if ($row['excepcion'] === "OMISION DE PONCHADO" || $row['excepcion'] === "SERVICIO EXTERNO") {
                        $mensaje .= $row;
                        $valido = true;
                    } else {
                        $mensaje .= ' Además, no tiene ninguna excepción aprobada en el sistema.';
                    }
                }else{
                    $mensaje = $row;
                    $valido = true;
                }
            }

            echo json_encode(['valido' => $valido, 'mensaje' => $mensaje]);
        } else {
            echo json_encode(['valido' => false, 'mensaje' => 'No se encontraron datos para el día seleccionado: ']);
        }
        break;
    case 'obtener_horario':
        // Recibe los datos enviados desde el cliente
        $fecha = $_POST['fecha'] ?? '';

        if (empty($fecha) || empty($codigoEmpleado)) {
            echo json_encode(['valido' => false, 'mensaje' => 'Los datos necesarios no están completos.']);
            exit;
        }

        // Suponiendo que $fecha es la fecha en formato 'Y-m-d'
        $timestamp = strtotime($fecha);
        $diaSemana = strtolower(date('l', $timestamp)); // Obtiene el día de la semana en inglés

        // Mapeo de nombres de días de la semana del inglés al español para tu base de datos
        $diaMap = [
            'monday'    => 'lunes',
            'tuesday'   => 'martes',
            'wednesday' => 'miercoles',
            'thursday'  => 'jueves',
            'friday'    => 'viernes',
            'saturday'  => 'sabado',
            'sunday'    => 'domingo'
        ];

        $diaColumna = $diaMap[$diaSemana] ?? null;

        if (!$diaColumna) {
            echo json_encode(['valido' => false, 'mensaje' => 'Día de la semana inválido.']);
            exit;
        }

        // Obtener horario del empleado
        $sqlHorario = "SELECT `$diaColumna` as horaEntrada FROM horarios WHERE codigo = :codigo";
        $stmtHorario = $pdo->prepare($sqlHorario);
        $stmtHorario->execute([':codigo' => $codigoEmpleado]);
        $horario = $stmtHorario->fetch(PDO::FETCH_ASSOC);
        if ($horario && !empty($horario['horaEntrada'])) {
            $horaInicioLaboral = $horario['horaEntrada'];
            $horaFinLaboral = date('H:i:s', strtotime($horaInicioLaboral . ' + 8 hours')); // Suma 8 horas a la hora de entrada
            $entradaHorario = DateTime::createFromFormat('H:i:s', $horaInicioLaboral);
            $salidaHorario = DateTime::createFromFormat('H:i:s', $horaFinLaboral);
            echo json_encode(['diaLaboral' => true, 'entrada' => $entradaHorario]);
        } else {
            echo json_encode(['diaLaboral' => false, 'entrada' => Null]);
        }
        break;
    case 'obtener_reporte':
        // Recibe los datos enviados desde el cliente
        $fecha = $_POST['fecha'] ?? '';
        if (empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Fecha no proporcionada.']);
            exit;
        }

        // Prepara la consulta SQL para obtener datos del reporte de horas extras
         $query = "SELECT fecha, TIME_FORMAT(hora_entrada, '%H:%i') AS hora_entrada, TIME_FORMAT(hora_salida, '%H:%i') AS hora_salida, descripcion, total_decimal, bono_dia_libre, estado  FROM reportes_horas_extras WHERE codigo_usuario = :codigo AND fecha = :fecha";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':codigo' => $codigoEmpleado, ':fecha' => $fecha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron datos para el día seleccionado.']);
        }
        break;
    case 'actualizar_descripcion':

        function actualizarDesc($codigo_usuario, $fecha, $descripcion) {
            global $pdo;  // Utiliza la conexión PDO que estableciste en tu archivo de conexión

            // Verifica que el código del usuario y la fecha sean del formato esperado
            if (!preg_match('/^\d+$/', $codigo_usuario)) {
                return ['success' => false, 'message' => 'Código de usuario inválido'];
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {  // Verifica que la fecha tenga el formato YYYY-MM-DD
                return ['success' => false, 'message' => 'Formato de fecha inválido'];
            }

            // Prepara la consulta SQL para actualizar la descripción en la base de datos
            $query = "UPDATE reportes_horas_extras SET descripcion = :descripcion WHERE codigo_usuario = :codigo AND fecha = :fecha";
            $stmt = $pdo->prepare($query);

            try {
                $stmt->execute([':descripcion' => $descripcion, ':codigo' => $codigo_usuario, ':fecha' => $fecha]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Descripción actualizada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se actualizó la descripción, puede que el registro no exista.']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la descripción: ' . $e->getMessage()]);
            }
        }

        // Recibir parámetros de AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo_usuario = $codigoEmpleado;
            $fecha = $_POST['fecha'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;

            if ($codigo_usuario && $fecha) {
                $response = actualizarDesc($codigo_usuario, $fecha, $descripcion);
                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            }
        }
        
        break;
    case 'crear_reporte':
        // Recibe los datos enviados desde el cliente
        $fecha = $_POST['fecha'] ?? '';
        $horaEntrada = $_POST['horaEntrada'] ?? null;
        $horaSalida = $_POST['horaSalida'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        if (empty($fecha) || empty($codigoEmpleado)) {
            echo json_encode(['valido' => false, 'mensaje' => 'Los datos necesarios no están completos.']);
            exit;
        }

        // Ajustar el código del empleado para la consulta en nómina
        $codigoEmpleadoCorto = ltrim($codigoEmpleado, '0');
        $sqlSueldo = "SELECT salario_mensual FROM nomina WHERE codigo = :codigo";
        $stmtSueldo = $pdo->prepare($sqlSueldo);
        $stmtSueldo->execute([':codigo' => $codigoEmpleadoCorto]);
        $sueldo = $stmtSueldo->fetchColumn();

        if (!$sueldo) {
            echo json_encode(['valido' => false, 'mensaje' => 'No se encontró el sueldo del empleado.']);
            exit;
        }
        
        // Calcular la tasa por hora
        $tasaHora = ($sueldo / 27.67) / 8;

        // Suponiendo que $fecha es la fecha en formato 'Y-m-d'
        $timestamp = strtotime($fecha);
        $diaSemana = strtolower(date('l', $timestamp)); // Obtiene el día de la semana en inglés

        // Mapeo de nombres de días de la semana del inglés al español para tu base de datos
        $diaMap = [
            'monday'    => 'lunes',
            'tuesday'   => 'martes',
            'wednesday' => 'miercoles',
            'thursday'  => 'jueves',
            'friday'    => 'viernes',
            'saturday'  => 'sabado',
            'sunday'    => 'domingo'
        ];

        $diaColumna = $diaMap[$diaSemana] ?? null;

        if (!$diaColumna) {
            echo json_encode(['valido' => false, 'mensaje' => 'Día de la semana inválido.']);
            exit;
        }

        // Obtener horario del empleado
        $sqlHorario = "SELECT `$diaColumna` as horaEntrada FROM horarios WHERE codigo = :codigo";
        $stmtHorario = $pdo->prepare($sqlHorario);
        $stmtHorario->execute([':codigo' => $codigoEmpleado]);
        $horario = $stmtHorario->fetch(PDO::FETCH_ASSOC);

        if ($horario && !empty($horario['horaEntrada'])) {
            $horaInicioLaboral = $horario['horaEntrada'];
            $horaFinLaboral = date('H:i:s', strtotime($horaInicioLaboral . ' + 8 hours')); // Suma 8 horas a la hora de entrada
            $entradaHorario = DateTime::createFromFormat('H:i:s', $horaInicioLaboral);
            $salidaHorario = DateTime::createFromFormat('H:i:s', $horaFinLaboral);
            $diaLaboral = true;
        } else {
            $horaInicioLaboral = null;
            $entradaHorario = null;
            $salidaHorario = null;
            $diaLaboral = false;
        }


        // Prepara la consulta SQL
        $query = "SELECT entrada, salida, excepcion FROM Ponchado_Sheets WHERE codigo = :codigo AND fecha = :fecha";

        // Prepara y ejecuta la consulta
        $stmt = $pdo->prepare($query);
        $stmt->execute([':codigo' => $codigoEmpleado, ':fecha' => $fecha]);
        $ponchado  = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ponchado ) {
            // Convertir las horas a objetos DateTime para comparación
            $ponchadoEntrada = DateTime::createFromFormat('H:i:s', $ponchado['entrada']);
            $ponchadoSalida = DateTime::createFromFormat('H:i:s', $ponchado['salida']);
            $ponchado ['excepcion'] = strtoupper($ponchado ['excepcion']); // Convierte a mayúsculas
            $mensaje = '';
            $valido = false;
            $errors = [];

            if (is_null($ponchado ['entrada']) && is_null($ponchado ['salida'])) {
                $mensaje = 'Faltan ambos ponchados para este día.';
                if ($ponchado ['excepcion'] === "OMISION DE PONCHADO" || $ponchado ['excepcion'] === "SERVICIO EXTERNO") {
                    $mensaje .= $ponchado ;
                    $valido = true;
                } else {
                    $mensaje .= ' Además, no tiene ninguna excepción aprobada en el sistema.';
                }
            } elseif (is_null($ponchado ['entrada']) || is_null($ponchado ['salida'])) {
                $ponchadoFaltante = is_null($ponchado ['entrada']) ? 'entrada' : 'salida';
                $mensaje = "Falta el ponchado de $ponchadoFaltante para este día.";
                if (!empty($ponchado ['excepcion']) && $ponchado ['excepcion'] !== "--") {
                    $mensaje .= $ponchado ;
                    $valido = true;
                } else {
                    $mensaje .= ' Además, no tiene una excepción aprobada y válida.';
                }
            } else {
                if ($ponchado ['entrada'] == $ponchado ['salida']) {
                    $mensaje = "Solo ponchó una sola vez en este día.";
                    if ($ponchado ['excepcion'] === "OMISION DE PONCHADO" || $ponchado ['excepcion'] === "SERVICIO EXTERNO") {
                        $mensaje .= $ponchado ;
                        $valido = true;
                    } else {
                        $mensaje .= ' Además, no tiene ninguna excepción aprobada en el sistema.';
                    }
                }else{
                    $mensaje = $ponchado ;
                    $valido = true;
                }
            }

            // Si tiene información de ponchado válida para el día reportado proceder a analizarla para ver si aplica o no reportar este día en función del ponchado de entrada y salida
            if ($valido) {
                if ($horaEntrada) {
                    $horaEntradaObj = DateTime::createFromFormat('H:i', $horaEntrada);

                    if ($horaEntradaObj <= $ponchadoEntrada || $horaEntradaObj >= $ponchadoSalida) {
                        $valido = false;
                        $errors[] = "La hora de entrada ($horaEntrada) no está dentro del intervalo de ponchado ({$ponchado['entrada']} - {$ponchado['salida']}).";
                    }
                    if ($diaLaboral) {
                        if ($horaEntradaObj > $entradaHorario && $horaEntradaObj < $salidaHorario) {
                            $valido = false;
                            $errors[] = "La hora de entrada ($horaEntrada) está dentro del horario para este empleado en este día [{$horaInicioLaboral} - {$horaFinLaboral}].";
                        }
                    }
                }

                if ($horaSalida) {
                    $horaSalidaObj = DateTime::createFromFormat('H:i', $horaSalida);
                    if ($horaSalidaObj <= $ponchadoEntrada || $horaSalidaObj >= $ponchadoSalida) {
                        $valido = false;
                        $errors[] = "La hora de salida ($horaSalida) no está dentro del intervalo de ponchado ({$ponchado['entrada']} - {$ponchado['salida']}).";
                    }
                    if ($diaLaboral) {
                        if ($horaSalidaObj > $entradaHorario && $horaSalidaObj < $salidaHorario) {
                            $valido = false;
                            $errors[] = "La hora de salida ($horaSalida) está dentro del horario para este empleado en este día ({$horaInicioLaboral} - {$horaFinLaboral}).";
                        }
                    }
                }
            }

            if ($valido) {
                // Si todas las validaciones son exitosas, calculamos las horas extras
                $entradaReportada = DateTime::createFromFormat('H:i', $horaEntrada);
                $salidaReportada = DateTime::createFromFormat('H:i', $horaSalida);
                $horasExtras = 0;
                $minutosExtras = 0;
                if ($entradaReportada && $salidaReportada) {

                    // Preparar mensaje como array para una estructura más organizada
                    if ($diaLaboral) {
                        // Si la entrada reportada es antes del inicio laboral
                        if ($entradaReportada < $entradaHorario) {
                            $intervaloAntes = $entradaHorario->diff($entradaReportada);
                            $horasExtras += $intervaloAntes->h;
                            $minutosExtras += $intervaloAntes->i;
                        }

                        // Si la salida reportada es después del fin laboral
                        if ($salidaReportada > $salidaHorario) {
                            $intervaloDespues = $salidaReportada->diff($salidaHorario);
                            $horasExtras += $intervaloDespues->h;
                            $minutosExtras += $intervaloDespues->i;
                        }

                        // Convertir el tiempo total a formato decimal y formato de tiempo
                        $totalHorasDecimal = $horasExtras + ($minutosExtras / 60);
                        $totalHorasTime = sprintf('%02d:%02d:00', $horasExtras, $minutosExtras);  // Formato de tiempo HH:MM:SS

                        $bonoDiaLibre = 0.00;
                        $tiempoDetalles = [
                            'Total_Horas' => $totalHorasTime,
                            'Total_Decimal' => number_format($totalHorasDecimal, 2)
                        ];
                    }else{
                        
                        $intervalo = $entradaReportada->diff($salidaReportada);
                        $horas = $intervalo->h;
                        $minutos = $intervalo->i;

                        // Convertir el tiempo total a formato decimal y formato de tiempo
                        $totalHorasDecimal = $horas + ($minutos / 60);
                        $totalHorasTime = sprintf('%02d:%02d:00', $horasExtras, $minutosExtras);  // Formato de tiempo HH:MM:SS

                        $bonoDiaLibre = $totalHorasDecimal * 0.30;
                        // Formatear a dos decimales
                        $bonoDiaLibre = number_format($bonoDiaLibre, 2);
                        $totalHorasDecimal = number_format($totalHorasDecimal + $bonoDiaLibre, 2);
                        $tiempoDetalles = [
                            'Total_Horas' => $totalHorasTime,
                            'SubTotal_Decimal' => number_format($totalHorasDecimal - $bonoDiaLibre, 2),
                            'bonoDiaLibre' => $bonoDiaLibre,
                            'Total_Decimal' => $totalHorasDecimal
                        ];
                    }
                    $montoDia = number_format($totalHorasDecimal * $tasaHora, 2);

                    // Intenta encontrar un registro existente primero
                    $sqlExist = "SELECT id_reporte FROM reportes_horas_extras WHERE codigo_usuario = ? AND fecha = ?";
                    $stmtExist = $pdo->prepare($sqlExist);
                    $stmtExist->execute([$codigoEmpleado, $fecha]);
                    $exist = $stmtExist->fetchColumn();

                    if ($exist) {
                        // Actualiza el registro existente
                        $sqlUpdate = "UPDATE reportes_horas_extras SET hora_entrada = ?, hora_salida = ?, descripcion = ?, total_decimal = ?, bono_dia_libre = ?, monto_dia = ?, total_horas = ?, horario_entrada = ? WHERE id_reporte = ?";
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        try {
                            $stmtUpdate->execute([$horaEntrada, $horaSalida, $descripcion, $totalHorasDecimal, $bonoDiaLibre ,$montoDia, $totalHorasTime, $horaInicioLaboral, $exist]);
                            echo json_encode(['valido' => true, 'mensaje' => 'Las horas extras han sido validadas correctamente y el reporte fue actualizado en la BD.', 'tiempo' => $tiempoDetalles]);
                        } catch (PDOException $e) {
                            echo json_encode(['valido' => false, 'mensaje' => 'Las horas extras fueron validadas correctamente pero hubo un error al actualizar el reporte: ' . $e->getMessage()]);
                        }
                    } else {
                        echo json_encode(['valido' => false, 'mensaje' => 'Error inesperado. No se encontró un reporte existente para actualizar.En este punto, debe de haber un reporte ya creado para el día en cuestión']);
                    }
                }else{
                    echo json_encode(['valido' => $valido, 'mensaje' => $mensaje]);
                }
            } else {
                echo json_encode(['valido' => false, 'mensaje' => implode(" ", $errors)]);
            }

        } else {
            echo json_encode(['valido' => false, 'mensaje' => 'No se encontraron datos para el día seleccionado: ']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
}
?>
