<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

// 1. IMPORTANTE: Iniciar la gestión de sesión
session_start();

header("Content-Type: application/json");

require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new controller();
$user = $controller->loginUser($username, $password);

if (is_null($user)) {
    $admin = $controller->loginAdmin($username, $password);
    
    if (is_null($admin)) {
        echo json_encode(["error" => 'Datos incorrectos'], JSON_UNESCAPED_UNICODE);
    } else {
        // AQUÍ EL CAMBIO: Añadimos "rol" => "admin"
        echo json_encode([
            "resultado" => $admin, 
            "rol" => "admin" 
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // AQUÍ EL CAMBIO: Añadimos "rol" => "user"
    echo json_encode([
        "resultado" => $user, 
        "rol" => "user" 
    ], JSON_UNESCAPED_UNICODE);
}
?>