<?php
require_once '../Config/Session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado.',
        'data' => null
    ]);
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
    ]);
    exit;
}

$password = $input['password'] ?? '';

if (empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos',
        'data' => null
    ]);
    exit;
}

$profile_code = (string) $_SESSION['user']['profile_code'];
$password = trim($password);

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$controller = new ProfileController();
$modify = $controller->modifyPassword($profile_code, $passwordHash);

if ($modify) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Contraseña modificada correctamente',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al modificar la contraseña',
        'data' => null
    ]);
}
?>
