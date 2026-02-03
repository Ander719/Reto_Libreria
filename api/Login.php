<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/ProfileController.php';
require_once '../config/Session.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new ProfileController();
// El método loginUser ya devuelve un array con 'success' y 'status_code'
$response = $controller->loginUser($username, $password);

http_response_code($response['status_code']); 
echo json_encode($response);
?>