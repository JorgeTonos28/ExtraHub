<?php
session_start();

// Conexión a la base de datos
include 'includes/pdo_db_connect.php';

// Verificar si se recibieron datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Email no válido
        $_SESSION['error'] = 'Datos de inicio de sesión inválidos';
        header("Location: login.php");
        exit;
    }

    // Consultar la base de datos
    $query = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $query->bindParam(':correo', $correo);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe y la contraseña es correcta
    if ($user && password_verify($password, $user['contraseña_hash'])) {
        // Usuario autenticado
        $_SESSION['user_id'] = $user['codigo'];
        $_SESSION['departamento_id'] = $user['departamento_id'];

        // Obtener le gerencia_id
        $departamentoId = $user['departamento_id'];
        $query = $pdo->prepare("SELECT gerencia_id FROM departamentos WHERE id_departamento = :departamentoId");
        $query->bindParam(':departamentoId', $departamentoId);
        $query->execute();
        $gerenciaId = $query->fetchColumn();

        // Guardar el gerencia_id en la sesión
        if ($gerenciaId) {
            $_SESSION['gerencia_id'] = $gerenciaId;
        }

        // Obtener el rol del usuario
        $userId = $_SESSION['user_id']; // Asegúrate de haber obtenido el ID del usuario
        $query = $pdo->prepare("SELECT r.nombre_rol FROM usuario_roles ur JOIN roles r ON ur.id_rol = r.id_rol WHERE ur.codigo = :userId");
        $query->bindParam(':userId', $userId);
        $query->execute();
        $userRole = $query->fetchColumn();

        // Guardar el rol en la sesión
        if ($userRole) {
            $_SESSION['user_role'] = $userRole;
        }

        // Obtener el rol_id del usuario
        $query = $pdo->prepare("SELECT r.id_rol FROM usuario_roles ur JOIN roles r ON ur.id_rol = r.id_rol WHERE ur.codigo = :userId");
        $query->bindParam(':userId', $userId);
        $query->execute();
        $userRoleID = $query->fetchColumn();

        // Guardar el rol_id en la sesión
        if ($userRoleID) {
            $_SESSION['user_role_id'] = $userRoleID;
        }

        // Consultar las aplicaciones a las que el usuario tiene acceso
        $query = $pdo->prepare("SELECT a.nombre_app FROM usuario_aplicaciones ua JOIN aplicaciones a ON ua.id_app = a.id_app WHERE ua.codigo = :userId");
        $query->bindParam(':userId', $userId);
        $query->execute();
        $appsAccesibles = $query->fetchAll(PDO::FETCH_COLUMN);

        // Guardar las aplicaciones en la sesión
        if ($appsAccesibles) {
            $_SESSION['apps_accesibles'] = $appsAccesibles;
        }

        if (isset($_SESSION['last_visited'])) {
            $redirect_url = $_SESSION['last_visited'];
            unset($_SESSION['last_visited']); // Eliminar la URL almacenada después de usarla

            echo json_encode(['success' => true, 'redirect' => $redirect_url]);
            exit;
        } else {
            // Redirigir a una página predeterminada si 'last_visited' no está establecido
            echo json_encode(['success' => true, 'redirect' => 'index.php']);
            exit;
        }
    } else {
        // Autenticación fallida
        echo json_encode(['success' => false, 'error' => 'Correo o contraseña incorrectos']);
        exit;
    }
}

// Si se accede al archivo de forma incorrecta, no se accede a través del formulario
header('Location: index.php');
exit;
?>
