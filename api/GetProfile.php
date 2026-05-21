<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Config/Session.php';
require_once '../controller/ProfileController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Acceso denegado',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$userId = $_SESSION['user']['profile_code'];
$userRole = $_SESSION['user']['role'];

$controller = new ProfileController();
$profileEntity = $controller->getProfile($userId, $userRole);

if ($profileEntity) {
    $profileData = $profileEntity->toArray();
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Perfil obtenido correctamente',
        'data' => [
            'user' => $profileData,
            'role' => $userRole
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'code' => 404,
        'message' => 'Perfil no encontrado',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
