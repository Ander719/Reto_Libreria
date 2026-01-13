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
    $_SESSION['profile_code'] = $admin['PROFILE_CODE'];
    $_SESSION['username'] = $admin['USER_NAME'];
    $_SESSION['role'] = 'admin';
    $_SESSION['isAdmin'] = true;

    echo json_encode(["exito" => true, "rol" => "admin", "resultado" => $admin]);
    exit;
}

// 2. Probar Usuario
$user = $controller->loginUser($username, $password);
if ($user) {
    $_SESSION['profile_code'] = $user['PROFILE_CODE'];
    $_SESSION['username'] = $user['USER_NAME'];
    $_SESSION['role'] = 'user';
    $_SESSION['isAdmin'] = false;

    echo json_encode(["exito" => true, "rol" => "user", "resultado" => $user]);
    exit;
}

echo json_encode(["exito" => false, "error" => "Credenciales incorrectas"]);
?>