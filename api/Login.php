<?php
// api/Login.php
session_start();
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new controller();

// 1. Probar Admin PRIMERO (para dar prioridad a privilegios)
$admin = $controller->loginAdmin($username, $password);
if ($admin) {
    $_SESSION['logged_in'] = true;

    $_SESSION['user_data'] = [
        'id' => $admin['profile_code'],
        'user_name' => $admin['user_name'], // En JS usarás currentUser.nombre
        'role' => 'admin'
    ];

    echo json_encode([
        "success" => true,
        "role" => "admin",
        "user" => $_SESSION['user_data']
    ]);
    exit;
}

// 2. Probar Usuario
$user = $controller->loginUser($username, $password);
if ($user) {
    $_SESSION['logged_in'] = true;

    $_SESSION['user_data'] = [
        'id' => $user['profile_code'],
        'user_name' => $user['user_name'], // En JS usarás currentUser.nombre
        'role' => 'user'
    ];

    echo json_encode([
        "success" => true,
        "role" => "user",
        "user" => $_SESSION['user_data']
    ]);
    exit;
}

echo json_encode(["exito" => false, "error" => "Credenciales incorrectas"]);
