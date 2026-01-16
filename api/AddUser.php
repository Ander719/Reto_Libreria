<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../controller/ProfileController.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$pswd = $input['pswd1'] ?? '';

// 3. Validaciones básicas de entrada
if (empty($username) || empty($pswd)) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

// 4. Delegamos al Controlador
$authController = new ProfileController();
$response = $authController->register($username, $pswd);

// 5. Devolvemos la respuesta
echo json_encode($response);
