<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../config/Session.php';
require_once '../controller/ProfileController.php';

    if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Acceso denegado"]);
        exit;
    }

$userId = $_SESSION['user']['profile_code'];
$userRole = $_SESSION['user']['role'];

$controller = new ProfileController();
$data = $controller->getProfile($userId, $userRole);

    if ($data) {
        unset($data['pswd']); 
        unset($data['password']); 
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "user" => $data,
            "role" => $userRole
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Perfil no encontrado"]);
    }
?>