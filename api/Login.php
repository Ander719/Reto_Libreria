<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$username = trim(htmlspecialchars($username));
$password = trim($password);

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos vacíos',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$controller = new ProfileController();
$identity = $controller->loginUser($username);

if (!$identity || !isset($identity['profile']) || !isset($identity['role'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Usuario o contraseña incorrectos',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$profile = $identity['profile'];
$role = $identity['role'];

if (!password_verify($password, $profile->getPswd())) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Usuario o contraseña incorrectos',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Regeneramos el ID de sesión tras autenticación para mitigar session fixation.
session_regenerate_id(true);

$_SESSION['user'] = [
    'profile_code' => $profile->getProfile_code(),
    'user_name' => $profile->getUser_name(),
    'role' => $role
];

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Inicio de sesión correcto',
    'data' => [
        'role' => $role,
        'user' => $_SESSION['user']
    ]
]);
?>
