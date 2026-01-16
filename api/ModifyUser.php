<?php
// api/ModifyUser.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

// 1. VALIDACIÓN DE SESIÓN
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    echo json_encode(['exito' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

// Datos del usuario logueado
$loggedUserId = $_SESSION['user_data']['id'];
$loggedUserRole = $_SESSION['user_data']['rol'] ?? 'user';

// 2. RECOGIDA DE DATOS
$targetId = $_POST['target_id'] ?? $loggedUserId; // Si no hay target, es edición propia
$roleForm = $_POST['role'] ?? 'user'; // 'admin' o 'user' (qué tipo de formulario se envió)

// Datos comunes
$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telephone = $_POST['phone'] ?? '';

// 3. VERIFICACIÓN DE PERMISOS (CRÍTICO)
// Solo puedes editar si: Eres tú mismo O si eres Administrador
if ($targetId != $loggedUserId && $loggedUserRole !== 'admin') {
    echo json_encode(['exito' => false, 'error' => 'No tienes permiso para modificar este usuario']);
    exit;
}

$controller = new controller();
$result = false;

// 4. DERIVAR AL CONTROLADOR CORRECTO
if ($roleForm === 'admin') {
    // --- EDICIÓN DE ADMINISTRADOR ---
    // Recogemos campo específico de Admin
    $currentAccount = $_POST['accountNumber'] ?? '';
    
    // Llamamos a modifyAdmin
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $currentAccount, $targetId);

} else {
    // --- EDICIÓN DE USUARIO NORMAL ---
    // Recogemos campos específicos de User
    $cardNumber = $_POST['cardNumber'] ?? '';
    $gender = $_POST['gender'] ?? 'Other';
    
    // Llamamos a modifyUser
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $cardNumber, $targetId);
}

// 5. RESPUESTA
if ($result) {
    // Si me modifiqué a mí mismo, actualizamos la sesión por si cambié el nombre
    if ($targetId == $loggedUserId) {
        $_SESSION['user_data']['nombre'] = $username;
    }
    echo json_encode(['exito' => true, 'message' => 'Datos actualizados correctamente']);
} else {
    echo json_encode(['exito' => false, 'error' => 'Error al actualizar en la base de datos']);
}
?>