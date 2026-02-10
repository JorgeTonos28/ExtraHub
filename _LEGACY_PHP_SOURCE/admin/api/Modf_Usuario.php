<?php
header('Content-Type: application/json; charset=utf-8');

include_once '../../includes/pdo_db_connect.php'; // Ajusta la ruta según sea necesario

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización y Validación de los Datos
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cargo = filter_var($_POST['cargo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $departamentoId = filter_input(INPUT_POST, 'departamento', FILTER_SANITIZE_NUMBER_INT);
    $nivelCorporativo = filter_input(INPUT_POST, 'nivelCorporativo', FILTER_SANITIZE_NUMBER_INT);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $pc_user = "PC_Goes_Here-02554". bin2hex(random_bytes(4));
    $contrasena = filter_var($_POST['contrasena'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
    $idRol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_NUMBER_INT);
    $tipoHorario = filter_var($_POST['tipoHorario'] ?? 'fijo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);// Si no se recibe, por defecto es 'fijo'
    $horario = [
        'lunes' => filter_var($_POST['lunes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'martes' => filter_var($_POST['martes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'miercoles' => filter_var($_POST['miercoles'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'jueves' => filter_var($_POST['jueves'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'viernes' => filter_var($_POST['viernes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'sabado' => filter_var($_POST['sabado'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'domingo' => filter_var($_POST['domingo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    ];
    $preferenciaFirma = isset($_POST['preferencia_firma']) ? htmlspecialchars($_POST['preferencia_firma'], ENT_QUOTES, 'UTF-8') : '';
    $account_status = 1;   

    // Sanitización del array de aplicaciones
    $aplicacionesSeleccionadas = isset($_POST['apps']) ? $_POST['apps'] : array();
    $aplicacionesLimpias = array();
    foreach ($aplicacionesSeleccionadas as $idApp) {
        $idAppLimpiado = filter_var($idApp, FILTER_SANITIZE_NUMBER_INT);
        if ($idAppLimpiado) {
            $aplicacionesLimpias[] = $idAppLimpiado;
        }
    }
    try {
        $pdo->beginTransaction();

        // 1. Validación para Administradores Generales
        if ($idRol == 5) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario_roles WHERE id_rol = ? AND codigo != ?");
            $stmt->execute([5, $codigo]);
            if ($stmt->fetchColumn() >= 2) {
                echo json_encode(["error" => "No se pueden crear más de 2 administradores generales a nivel nacional"]);
                exit;
            }
        }

        // 2. Validación para Administradores por Gerencia
        if ($idRol == 1) {
            $stmt = $pdo->prepare("SELECT nombre FROM nombres_gerencias WHERE id_gerencia = (SELECT gerencia_id FROM departamentos WHERE id_departamento = ?)");
            $stmt->execute([$departamentoId]);
            $nombreGerencia = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE departamento_id IN (SELECT id_departamento FROM departamentos WHERE gerencia_id = (SELECT gerencia_id FROM departamentos WHERE id_departamento = ?)) AND codigo != ? AND codigo IN (SELECT codigo FROM usuario_roles WHERE id_rol = ?)");
            $stmt->execute([$departamentoId, $codigo, 1]);
            if ($stmt->fetchColumn() >= 3) {
                echo json_encode(["error" => "Ya existen 3 administradores en la " . $nombreGerencia]);
                exit;
            }
        }

        // 3. Validación para Encargados por Departamento
        if ($idRol == 2) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario_roles WHERE id_rol = ? AND codigo != ? AND codigo IN (SELECT codigo FROM usuarios WHERE departamento_id = ?)");
            $stmt->execute([2, $codigo, $departamentoId]);
            if ($stmt->fetchColumn() >= 1) {
                echo json_encode(["error" => "Ya existe un encargado en este departamento"]);
                exit;
            }
        }

        // 4. Validación para Ayudantes por Departamento
        if ($idRol == 3) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario_roles WHERE id_rol = ? AND codigo != ? AND codigo IN (SELECT codigo FROM usuarios WHERE departamento_id = ?)");
            $stmt->execute([3, $codigo, $departamentoId]);
            if ($stmt->fetchColumn() >= 3) {
                echo json_encode(["error" => "Ya existen 3 ayudantes en este departamento"]);
                exit;
            }
        }

        // 5. Validación de Correo Electrónico
        $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE correo = ? AND codigo != ?");
        $stmt->execute([$correo, $codigo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(["error" => "El correo electrónico ya está registrado"]);
            exit;
        }

        // 6. Validar que los tiempos sean correctos si se proporcionan
        foreach ($horario as $dia => $hora) {

            if ($hora && !preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $hora)) {
                // Manejar la situación donde el formato de la hora no es válido
                echo json_encode(["error" => "Formato de hora no válido para " . $dia]);
                $pdo->rollback();
                exit;
            }
        }

        // 7. Validar restricción usuarios horas extras
        if ($idRol == 4 && in_array(1, $aplicacionesLimpias)) {
            $cargo = strtoupper($cargo);
            $cargosPermitidos = [
                'CHOFER', 'CONSERJE', 'MENSAJERO INTERNO', 'MENSAJERO EXTERNO', 'OFICIAL DE SEGURIDAD',
                'PINTOR', 'PLOMERO', 'CAMARERO', 'JARDINERO', 'LAVADOR DE VEHÍCULO',
                'AUXILIAR DE ALMACÉN', 'TÉCNICO DE MANTENIMIENTO', 'ANALISTA DE CCTV',
                'AUXILIAR DE SERVICIOS GENERALES', 'AUXILIAR DE TRANSPORTACIÓN',
                'AUXILIAR DE EVENTOS', 'ASISTENTE DE TRANSPORTACIÓN'
            ];

            if (!in_array($cargo, $cargosPermitidos)) {
                echo json_encode(["error" => "La creación de horas extras está permitida únicamente para el personal de apoyo que pertenezca al 5to nivel corporativo. Para agregar este usuario al sistema, elimine la app (Horas Extras) de la lista de apps."]);
                $pdo->rollback();
                exit;
            }
        }

        // Hash de la contraseña
        $contrasenaHash = $contrasena ? password_hash($contrasena, PASSWORD_DEFAULT) : null;

        // Preparar consulta para actualizar usuario
        $sql = "UPDATE usuarios SET departamento_id = ?, Nivel_Corporativo = ?, correo = ?, pc_user = ?, preferencia_firma = ?";
        $parametros = [$departamentoId, $nivelCorporativo, $correo, $pc_user, $preferenciaFirma];

        // Si se proporcionó una nueva contraseña, añadirla a la consulta
        if ($contrasenaHash) {
            $sql .= ", contraseña_hash = ?";
            $parametros[] = $contrasenaHash;
        }

        // Completar la consulta
        $sql .= " WHERE codigo = ?";
        $parametros[] = $codigo;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);

        // Actualizar el rol del usuario
        $stmt = $pdo->prepare("UPDATE usuario_roles SET id_rol = ? WHERE codigo = ?");
        $stmt->execute([$idRol, $codigo]);

        // Actualizar accesos a aplicaciones para el usuario
        // Primero, eliminar todas las aplicaciones existentes
        $stmt = $pdo->prepare("DELETE FROM usuario_aplicaciones WHERE codigo = ?");
        $stmt->execute([$codigo]);

        // Luego, insertar las nuevas aplicaciones
        $stmt = $pdo->prepare("INSERT INTO usuario_aplicaciones (codigo, id_app) VALUES (?, ?)");
        foreach ($aplicacionesLimpias as $idApp) {
            // Asegurarse de que $idApp está sanitizado
            $stmt->execute([$codigo, $idApp]);
        }

        // Preparar los valores de horario, convirtiendo cadenas vacías en NULL
        $horarios = [
            $horario['lunes'], $horario['martes'], $horario['miercoles'], 
            $horario['jueves'], $horario['viernes'], $horario['sabado'], 
            $horario['domingo']
        ];
        $horariosPreparados = array_map(function($hora) {
            return empty($hora) ? null : $hora;
        }, $horarios);

        // Actualizar horario
        $stmt = $pdo->prepare("UPDATE horarios SET tipo_horario = ?, lunes = ?, martes = ?, miercoles = ?, jueves = ?, viernes = ?, sabado = ?, domingo = ? WHERE codigo = ?");
        $stmt->execute(array_merge([$tipoHorario], $horariosPreparados, [$codigo]));

        // Terminar
        $pdo->commit();

        echo json_encode(["success" => "Usuario modificado exitosamente"]);


    } catch (PDOException $e) {
        $pdo->rollback();
        echo json_encode(["error" => "Error al modificar usuario: ". $departamentoId . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Método no permitido"]);
}
?>

