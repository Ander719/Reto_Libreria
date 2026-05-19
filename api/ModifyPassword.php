<?php
// api/ModifyPassword.php — Endpoint para cambiar la contraseña de un usuario
// Requisito profesor: gestión de sesiones, validaciones server-side

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/ProfileController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para cambiar la contraseña.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado. Debes iniciar sesión.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida de datos: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$profile_code = isset($jsonInput['profile_code']) ? (int)$jsonInput['profile_code'] : (isset($_POST['profile_code']) ? (int)$_POST['profile_code'] : 0);
$password = isset($jsonInput['password']) ? $jsonInput['password'] : (isset($_POST['password']) ? $_POST['password'] : '');

// 6. Validación server-side: campos obligatorios
if ($profile_code <= 0 || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ID de usuario y nueva contraseña son obligatorios.',
        'data' => null
    ]);
    exit;
}

// 7. Validación de longitud mínima de contraseña
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'La contraseña debe tener al menos 8 caracteres.',
        'data' => null
    ]);
    exit;
}

// 8. Verificación de permisos: solo puedes cambiar tu propia contraseña o ser admin
$loggedUserId = $_SESSION['user']['profile_code'];
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

if ($profile_code !== $loggedUserId && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Permisos insuficientes para modificar esta contraseña.',
        'data' => null
    ]);
    exit;
}

// 9. Generación del hash de contraseña con password_hash
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 10. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 11. Actualización de la contraseña en la base de datos
$result = $controller->modifyPassword($profile_code, $passwordHash);

// 12. Respuesta según resultado
if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Contraseña actualizada correctamente.',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al procesar el cambio de contraseña en la base de datos.',
        'data' => null
    ]);
}
?>
