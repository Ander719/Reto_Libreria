<?php
// api/GetProfile.php
header("Content-Type: application/json; charset=utf-8");
require_once '../config/Session.php';
require_once '../controller/ProfileController.php';

// 1. CONTROL DE SEGURIDAD: ¿Tiene llave?
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401); // No autorizado
    echo json_encode(["success" => false, "error" => "Acceso denegado"]);
    exit;
}

// 2. RECUPERAR DATOS DE LA SESIÓN (Nuestra fuente de verdad)
$userId = $_SESSION['user']['profile_code'];
$userRole = $_SESSION['user']['role'];

// 3. PEDIR DATOS COMPLETOS A LA BD
$controller = new ProfileController();
$data = $controller->getProfile($userId, $userRole);

if ($data) {
    // OJO: Por seguridad extrema, borramos la contraseña del array antes de enviarla
    unset($data['pswd']); 
    unset($data['password']); 

    echo json_encode([
        "success" => true,
        "user" => $data,
        "role" => $userRole
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Perfil no encontrado"]);
}
?>