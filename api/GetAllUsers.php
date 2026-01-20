<?php
// api/GetAllUsers.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// 1. CORRECCIÓN: Usar ProfileController
require_once '../controller/ProfileController.php';

session_start();

// Opcional: Verificar si es admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['current_account'])) {
    // Si quieres bloquear accesos no autorizados:
    // echo json_encode(['error' => 'Acceso denegado']);
    // exit;
}

$controller = new ProfileController();
$users = $controller->get_all_users();

if ($users) {
    // Convertimos claves a minúsculas para asegurar compatibilidad con JS
    $users = array_map(function($user) {
        return array_change_key_case($user, CASE_LOWER);
    }, $users);

    echo json_encode([
        'resultado' => $users
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['resultado' => []]);
}
?>