<?php
// api/GetProfile.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

// 1. CORRECCIÓN: Verificar si existe el array 'user_data' y el 'id' dentro de él
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    // Si quieres depurar, podrías descomentar esto temporalmente:
    // echo json_encode(['exito' => false, 'error' => 'Sesión vacía', 'debug' => $_SESSION]); exit;
    
    echo json_encode(['exito' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

// 2. CORRECCIÓN: Obtener el ID desde la ruta correcta
$id = $_SESSION['user_data']['id'];

$controller = new controller();
$user = $controller->getUserData($id); 

if ($user) {
    // Convertimos claves a minúsculas para evitar problemas de mayúsculas/minúsculas en JS
    $user = array_change_key_case($user, CASE_LOWER);

    if(isset($user['pswd'])) unset($user['pswd']); // Por seguridad

    echo json_encode(['exito' => true, 'user' => $user]);
} else {
    echo json_encode(['exito' => false, 'error' => 'No se encontraron datos para este usuario']);
}
?>