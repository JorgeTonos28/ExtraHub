<?php
include_once '../../includes/pdo_db_connect.php'; // Ajusta la ruta según sea necesario

$termino = $_GET['termino'] ?? '';
$campo = $_GET['campo'] ?? 'nombre'; // 'codigo', 'nombre' o 'cedula'

try {
    // Conectar a la base de datos
    $pdo->beginTransaction();

    // Búsqueda en la tabla nomina
    $query = "";
    if ($campo === 'codigo') {
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE codigo LIKE ?";
    } elseif ($campo === 'nombre') {
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE nombre LIKE ?";
    } else { // 'cedula'
        $query = "SELECT codigo, nombre, cedula, cargo, departamento, salario_mensual FROM nomina WHERE cedula LIKE ?";
    }

    $stmt = $pdo->prepare($query);
    $likeTermino = '%' . $termino . '%';
    $stmt->execute([$likeTermino]);
    $datosNomina = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si el código está en la tabla usuarios
    $codigoUsuario = $datosNomina[0]['codigo'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE codigo = ?");
    $stmt->execute([$codigoUsuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario && $campo === 'codigo') {
        // Usuario no encontrado en la tabla usuarios
        echo json_encode(["error" => "Usuario no encontrado en el sistema. Por favor, crea el usuario primero."]);
        exit;
    }

    // Obtener información de la gerencia
    $idDepartamento = $usuario['departamento_id'] ?? null;
    $gerenciaId = null;
    if ($idDepartamento) {
        $stmt = $pdo->prepare("SELECT id_gerencia FROM nombres_gerencias WHERE id_gerencia = (SELECT gerencia_id FROM departamentos WHERE id_departamento = ?)");
        $stmt->execute([$idDepartamento]);
        $gerenciaId = $stmt->fetchColumn();
    }

    // Obtener información del departamento
    $nombreDepartamento = null;
    if ($idDepartamento) {
        $stmt = $pdo->prepare("SELECT id_departamento FROM departamentos WHERE id_departamento = ?");
        $stmt->execute([$idDepartamento]);
        $nombreDepartamento = $stmt->fetchColumn();
    }

    // Obtener el Nivel Corporativo
    $nivelCorporativo = $usuario['Nivel_Corporativo'] ?? null;

    // Obtener el Correo
    $correoElectronico = $usuario['correo'] ?? null;

    // Obtener el Rol
    $rol = null;
    if ($usuario) {
        $stmt = $pdo->prepare("SELECT id_rol FROM usuario_roles WHERE codigo = ?");
        $stmt->execute([$codigoUsuario]);
        $idRol = $stmt->fetchColumn();

        if ($idRol) {
            $stmt = $pdo->prepare("SELECT id_rol FROM roles WHERE id_rol = ?");
            $stmt->execute([$idRol]);
            $rol = $stmt->fetchColumn();
        }
    }

    // Obtener las Aplicaciones asociadas al usuario
    $apps = [];
    if ($usuario) {
        $stmt = $pdo->prepare("SELECT id_app FROM usuario_aplicaciones WHERE codigo = ?");
        $stmt->execute([$codigoUsuario]);
        $appsIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($appsIds as $idApp) {
            $stmt = $pdo->prepare("SELECT nombre_app FROM aplicaciones WHERE id_app = ?");
            $stmt->execute([$idApp]);
            $nombreApp = $stmt->fetchColumn();
            if ($nombreApp) {
                $apps[] = $nombreApp . '_app';
            }
        }
    }

    // Obtener el Tipo de Horario
    $tipoHorario = null;
    if ($usuario) {
        $stmt = $pdo->prepare("SELECT tipo_horario FROM horarios WHERE codigo = ?");
        $stmt->execute([$codigoUsuario]);
        $tipoHorario = $stmt->fetchColumn();
    }

    // Obtener el Horario Detallado
    $horario = [];
    if ($usuario) {
        $stmt = $pdo->prepare("SELECT lunes, martes, miercoles, jueves, viernes, sabado, domingo FROM horarios WHERE codigo = ?");
        $stmt->execute([$codigoUsuario]);
        $resultadoHorario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($resultadoHorario) {
            $horario = [
                'lunes' => $resultadoHorario['lunes'],
                'martes' => $resultadoHorario['martes'],
                'miercoles' => $resultadoHorario['miercoles'],
                'jueves' => $resultadoHorario['jueves'],
                'viernes' => $resultadoHorario['viernes'],
                'sabado' => $resultadoHorario['sabado'],
                'domingo' => $resultadoHorario['domingo'],
            ];
        }
    }

    // Obtener la "Preferencia en firma"
    $preferenciaFirma = $usuario['preferencia_firma'] ?? null;

    // Combinar la información del usuario con los datos de la nómina, la gerencia y el departamento
    $datosNomina = $datosNomina[0] ?? [];

    $respuesta = [
        "codigo" => $datosNomina['codigo'],
        "nombre" => $datosNomina['nombre'],
        "cedula" => $datosNomina['cedula'],
        "cargo" => $datosNomina['cargo'],
        "departamento_id" => $idDepartamento,
        "departamento" => $nombreDepartamento,
        "salario_mensual" => $datosNomina['salario_mensual'],
        "usuario_en_sistema" => $usuario ? true : false,
        "gerencia" => $gerenciaId ?? '',
        "nivelCorporativo" => $nivelCorporativo ?? '',
        "correo" => $correoElectronico ?? '',
        "rol" => $rol ?? '',
        "apps" => $apps ?? [],
        "tipoHorario" => $tipoHorario ?? '',
        "horario" => $horario ?? [],
        "preferenciaFirma" => $preferenciaFirma ?? ''
    ];

    // Terminar transacción
    $pdo->commit();

    // 5. Devolver los datos en formato JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($respuesta);

} catch (PDOException $e) {
    $pdo->rollback();
    echo json_encode(["error" => "Error al buscar usuario: " . $e->getMessage()]);
}
?>
