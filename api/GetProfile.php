<?php
// api/GetProfile.php
header("Content-Type: application/json; charset=utf-8");
require_once '../config/Session.php';
require_once '../controller/ProfileController.php';

// 1. CONTROL DE SEGURIDAD: ¿Tiene llave?
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    http_response_code(401); // No autorizado
    echo json_encode(["success" => false, "error" => "Acceso denegado"]);
    exit;
}

// 2. RECUPERAR DATOS DE LA SESIÓN (Nuestra fuente de verdad)
$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

// 3. PEDIR DATOS COMPLETOS A LA BD
$controller = new ProfileController();
$fullData = $controller->getProfileData($userId, $userRole);

if ($fullData) {
    // Convertimos el objeto (User o Admin) a Array
    $dataArray = $fullData->toArray(); 
    
    // OJO: Por seguridad extrema, borramos la contraseña del array antes de enviarla
    unset($dataArray['pswd']); 
    unset($dataArray['password']); 

    echo json_encode([
        "success" => true,
        "user" => $dataArray,
        "role" => $userRole
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Perfil no encontrado"]);
}
?>