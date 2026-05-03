<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
session_start();

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
