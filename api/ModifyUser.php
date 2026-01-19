<?php
// api/ModifyUser.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

// 1. VALIDACIÓN DE SESIÓN (Nueva estructura)
if (!isset($_SESSION['user'])) {
    echo json_encode(['exito' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

// Recuperamos datos de la sesión nueva (ProfileController guarda 'user')
$userSession = $_SESSION['user'];
$loggedUserId = $userSession['profile_code'] ?? null;

// Detectar si es admin revisando si tiene cuenta corriente o el rol explícito
$isAdmin = isset($userSession['current_account']) || ($userSession['role_type'] ?? '') === 'admin';

if (!$loggedUserId) {
    echo json_encode(['exito' => false, 'error' => 'Error de sesión: ID no encontrado']);
    exit;
}

// 2. RECOGIDA DE DATOS
$targetId = $_POST['target_id'] ?? $loggedUserId; // Si no hay target, es edición propia
$roleForm = $_POST['role'] ?? 'user';

// Datos comunes
$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telephone = $_POST['phone'] ?? '';

// 3. VERIFICACIÓN DE PERMISOS
// Solo puedes editar si: Eres tú mismo O si eres Administrador
if ($targetId != $loggedUserId && !$isAdmin) {
    echo json_encode(['exito' => false, 'error' => 'No tienes permiso para modificar este usuario']);
    exit;
}

$controller = new ProfileController();
$result = false;

// 4. DERIVAR AL CONTROLADOR
if ($roleForm === 'admin') {
    $currentAccount = $_POST['accountNumber'] ?? '';
    // modifyAdmin: email, username, telephone, name, surname, account, profile_code
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $currentAccount, $targetId);

} else {
    $cardNumber = $_POST['cardNumber'] ?? '';
    $gender = $_POST['gender'] ?? 'Other';
    // modifyUser: email, username, telephone, name, surname, gender, card_no, profile_code
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $cardNumber, $targetId);
}

// 5. RESPUESTA
if ($result) {
    // Si te has editado a ti mismo, actualizamos la sesión para que se vea el cambio al instante
    if ($targetId == $loggedUserId) {
        $_SESSION['user']['user_name'] = $username;
        $_SESSION['user']['name_'] = $name;
        // Actualiza otros campos si es necesario
    }
    echo json_encode(['exito' => true, 'message' => 'Datos actualizados correctamente']);
} else {
    echo json_encode(['exito' => false, 'error' => 'Error al actualizar en la base de datos']);
}
?>