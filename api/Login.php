<?php
// api/Login.php — Endpoint para la autenticación de usuarios
// Requisito profesor: validaciones server-side, gestión de sesiones con comentarios descriptivos

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session (gestión de sesiones), Database (conexión PDO), Controller (lógica)
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/ProfileController.php';

// 3. Validación del método HTTP (requisito profesor: códigos HTTP correctos)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para iniciar sesión.',
        'data' => null
    ]);
    exit;
}

// 4. Recogida de datos de entrada: soportamos tanto JSON como form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$username = isset($jsonInput['username']) ? trim($jsonInput['username']) : (isset($_POST['username']) ? trim($_POST['username']) : '');
$password = isset($jsonInput['password']) ? $jsonInput['password'] : (isset($_POST['password']) ? $_POST['password'] : '');

// 5. Saneamiento del username para prevenir XSS
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

// 6. Validación server-side: campos obligatorios (requisito profesor)
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El nombre de usuario y la contraseña son obligatorios.',
        'data' => null
    ]);
    exit;
}

// 7. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 8. Búsqueda del usuario: primero admin, luego usuario estándar
$foundUser = $controller->findAdminByUsername($username);
$role = 'admin';

if (!$foundUser) {
    $foundUser = $controller->findUserByUsername($username);
    $role = 'user';
}

// 9. Verificación de contraseña con hash seguro (password_verify)
if ($foundUser && password_verify($password, $foundUser->getPswd())) {
    // Convertimos el objeto a array y eliminamos el hash de la contraseña por seguridad
    $userData = $foundUser->toArray();
    unset($userData['pswd']);

    // Regeneramos el ID de sesión tras login exitoso para prevenir ataques de session fixation
    // Esto invalida el ID anterior y genera uno nuevo, evitando que un atacante lo reuse
    session_regenerate_id(true);

    // Creamos la sesión con los datos necesarios para identificar al usuario
    // Esto permite persistir el estado de autenticación entre peticiones
    $_SESSION['user'] = $userData;
    $_SESSION['user']['role'] = $role;

    // Respuesta de éxito con código 200
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Autenticación exitosa.',
        'data' => [
            'user' => $_SESSION['user'],
            'role' => $role
        ]
    ]);
} else {
    // Credenciales incorrectas: código 401 (Unauthorized)
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Credenciales de acceso incorrectas.',
        'data' => null
    ]);
}
?>
