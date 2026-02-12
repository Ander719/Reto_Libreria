<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
session_start();

$controller = new ProfileController();
$users = $controller->get_all_users();

    if ($users) {
        $users = array_map(function($user) {
            return array_change_key_case($user, CASE_LOWER);
        }, $users);
        http_response_code(200);
        echo json_encode(['resultado' => $users]);
    } else {
        http_response_code(200);
        echo json_encode(['resultado' => []]);
    }
?>