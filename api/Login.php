<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/ProfileController.php';
require_once '../Config/Session.php';

$data = json_decode(file_get_contents("php://input"), true);
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
    ]);
    exit;
}

$controller = new ProfileController();
$response = $controller->loginUser($username, $password);

$code = isset($response['status_code']) ? (int)$response['status_code'] : 500;
$isSuccess = !empty($response['success']);

http_response_code($code);
echo json_encode([
    'status' => $isSuccess ? 'success' : 'error',
    'code' => $code,
    'message' => $isSuccess ? 'Inicio de sesión correcto' : ($response['error'] ?? 'Error de autenticación'),
    'data' => $isSuccess ? [
        'role' => $response['role'] ?? null,
        'user' => $_SESSION['user'] ?? null
    ] : null
]);
?>
