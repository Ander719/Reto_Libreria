<?php
// api/ModifyAdmin.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// --- CORRECCIÓN: Usamos ProfileController ---
require_once '../controller/ProfileController.php';

// Nota: modifyAdmin suele venir por POST en tu ModifyUser, pero si aquí usas GET, lo mantenemos.
// Si prefieres usar JSON body como los demás, avísame.
$profile_code = $_GET['profile_code'] ?? '';
$email = $_GET['email'] ?? '';
$username = $_GET['username'] ?? '';
$telephone = $_GET['telephone'] ?? '';
$name = $_GET['name'] ?? '';
$surname = $_GET['surname'] ?? '';
$current_account = $_GET['current_account'] ?? '';

$controller = new ProfileController();
$modify = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);

if ($modify) {
    echo json_encode(['success' => true, 'message' => 'Admin modified correctly']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error modifying the admin']);
}
?>