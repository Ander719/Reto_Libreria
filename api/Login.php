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
        'nombre' => $admin['user_name'], // En JS usarás currentUser.nombre
        'rol' => 'admin'
    ];

    echo json_encode([
        "exito" => true,
        "rol" => "admin",
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
        'nombre' => $user['user_name'], // En JS usarás currentUser.nombre
        'rol' => 'user'
    ];

    echo json_encode([
        "exito" => true,
        "rol" => "user",
        "user" => $_SESSION['user_data']
    ]);
    exit;
}

echo json_encode(["exito" => false, "error" => "Credenciales incorrectas"]);
