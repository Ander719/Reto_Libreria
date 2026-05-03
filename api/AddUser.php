<?php
require_once '../controller/ProfileController.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
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
    ]);
    exit;
}

if (strlen($pswd) < 4) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'La contraseña debe tener al menos 4 caracteres',
        'data' => null
    ]);
    exit;
}

// 4. Delegamos al Controlador
$authController = new ProfileController();
$response = $authController->register($username, $pswd);

$isSuccess = !empty($response['success']);
$code = $isSuccess ? 200 : 400;

http_response_code($code);
echo json_encode([
    'status' => $isSuccess ? 'success' : 'error',
    'code' => $code,
    'message' => $isSuccess ? 'Usuario creado con éxito' : ($response['error'] ?? 'Error al crear usuario'),
    'data' => $isSuccess ? [
        'user' => $response['user'] ?? null
    ] : null
]);
