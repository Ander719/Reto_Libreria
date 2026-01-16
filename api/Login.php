<?php
// api/Login.php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/ProfileController.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new ProfileController();

$response = $controller->loginUser($username, $password);

// Devolvemos respuesta estandarizada (Rúbrica IL8.2)
http_response_code($response['status_code']); 
echo json_encode($response);