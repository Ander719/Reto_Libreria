<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ]);
    exit;
}

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

if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Acceso restringido a administradores.',
        'data' => null
    ]);
    exit;
}

$controller = new ProfileController();
$users = $controller->get_all_users();

if ($users) {
    $users = array_map(function ($userEntity) {
        return $userEntity->toArray();
    }, $users);
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Usuarios obtenidos correctamente.',
    'data' => $users ?: []
]);
?>
