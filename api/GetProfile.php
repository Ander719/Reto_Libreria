<?php
// api/GetProfile.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// CAMBIO: Usamos el nuevo ProfileController
require_once '../controller/ProfileController.php';

// 1. VALIDACIÓN DE SESIÓN (Nueva estructura)
if (!isset($_SESSION['user'])) {
    echo json_encode(['exito' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

// La nueva sesión guarda todo el objeto usuario. El ID suele ser 'profile_code'.
$userSession = $_SESSION['user'];
$id = $userSession['profile_code'] ?? null;

if (!$id) {
    echo json_encode(['exito' => false, 'error' => 'Error de sesión: ID no encontrado']);
    exit;
}

// 2. OBTENER DATOS ACTUALIZADOS
$controller = new ProfileController();
$user = $controller->getUserData($id); 

if ($user) {
    // Convertimos a minúsculas para asegurar compatibilidad con el JS
    $user = array_change_key_case($user, CASE_LOWER);
    
    // Quitamos la contraseña por seguridad
    if(isset($user['pswd'])) unset($user['pswd']); 

    echo json_encode(['exito' => true, 'user' => $user]);
} else {
    echo json_encode(['exito' => false, 'error' => 'No se encontraron datos']);
}
?>