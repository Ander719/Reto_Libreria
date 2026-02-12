<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

$profile_code = $_GET['profile_code'] ?? '';
$email = $_GET['email'] ?? '';
$username = $_GET['username'] ?? '';
$telephone = $_GET['telephone'] ?? '';
$name = $_GET['name'] ?? '';
$surname = $_GET['surname'] ?? '';
$current_account = $_GET['current_account'] ?? '';

$controller = new ProfileController();
$modify = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);

    if ($modify) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Admin modified correctly']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error modifying the admin']);
    }
?>