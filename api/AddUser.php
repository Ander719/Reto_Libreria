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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$username = $input['username'] ?? '';
$pswd = $input['pswd1'] ?? '';

$username = trim(htmlspecialchars($username));
$pswd = trim($pswd);

// 3. Validaciones básicas de entrada
if (empty($username) || empty($pswd)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan datos',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (strlen($pswd) < 8) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'La contraseña debe tener al menos 8 caracteres',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// 4. Hash de contraseña en API
$passwordHash = password_hash($pswd, PASSWORD_DEFAULT);

// 5. Delegamos al Controlador
$authController = new ProfileController();
$result = $authController->register($username, $passwordHash);

if ($result instanceof User) {
    $userData = $result->toArray();
    $_SESSION['user'] = [
        'profile_code' => $userData['profile_code'],
        'user_name' => $userData['user_name'],
        'role' => 'user'
    ];

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Usuario creado con éxito',
        'data' => [
            'user' => $userData
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($result === 'ERROR_DUPLICADO') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Ese nombre de usuario ya está cogido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

http_response_code(500);
echo json_encode([
    'status' => 'error',
    'code' => 500,
    'message' => 'Error al crear usuario',
    'data' => null
]);
