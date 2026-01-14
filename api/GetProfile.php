<?php
// api/GetProfile.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

if (!isset($_SESSION['profile_code'])) {
    echo json_encode(['exito' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

$id = $_SESSION['profile_code'];
$controller = new controller();
// Asegúrate de tener getUserById($id) en tu UserModel.php (te lo pasé antes)
$user = $controller->getUserData($id); 

if ($user) {
    unset($user['PSWD']); // Por seguridad
    echo json_encode(['exito' => true, 'user' => $user]);
} else {
    echo json_encode(['exito' => false, 'error' => 'No se encontraron datos']);
}
?>