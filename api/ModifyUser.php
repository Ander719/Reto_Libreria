<?php
// api/ModifyUser.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'No has iniciado sesión']); //
    exit;
}

$userSession = $_SESSION['user'];
$loggedUserId = $userSession['profile_code'] ?? null;
// Corrección: Validar rol 'admin' de forma robusta
$isAdmin = ($userSession['role'] ?? '') === 'admin'; 

$targetId = $_POST['target_id'] ?? $loggedUserId; 
$roleForm = $_POST['role'] ?? 'user';

// Validar permisos: Mismo usuario o Administrador
if ($targetId != $loggedUserId && !$isAdmin) {
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para modificar este usuario']); //
    exit;
}

$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telephone = $_POST['phone'] ?? '';

$controller = new ProfileController();
$result = false;

if ($roleForm === 'admin') {
    $currentAccount = $_POST['accountNumber'] ?? '';
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $currentAccount, $targetId); //
} else {
    $cardNumber = $_POST['cardNumber'] ?? '';
    $gender = $_POST['gender'] ?? 'Other';
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $cardNumber, $targetId); //
}

if ($result) {
    if ($targetId == $loggedUserId) {
        $_SESSION['user']['user_name'] = $username;
    }
    echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']); //
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar en la base de datos']); //
}
?>