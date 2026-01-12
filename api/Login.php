<?php
<<<<<<< HEAD
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

// 1. IMPORTANTE: Iniciar la gestión de sesión
session_start();

header("Content-Type: application/json");

=======
// api/Login.php
session_start();
header("Content-Type: application/json; charset=utf-8");
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
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