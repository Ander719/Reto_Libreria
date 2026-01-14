<?php
// api/modifyUser.php
session_start();
header('Content-Type: application/json');
require_once '../controller/controller.php';

// 1. Verificar Login
if (!isset($_SESSION['profile_code'])) {
    echo json_encode(['exito' => false, 'error' => 'No autorizado']);
    exit;
}

$controller = new controller();

// 2. Recoger datos comunes
$role = $_POST['role'] ?? 'user'; // 'admin' o 'user'
// Si viene un target_id (estamos editando a otro desde la tabla), lo usamos. Si no, usamos nuestro ID de sesión.
$targetId = !empty($_POST['target_id']) ? $_POST['target_id'] : $_SESSION['profile_code'];

$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$username = $_POST['username'] ?? ''; // El controlador suele pedir el username aunque no lo cambiemos

// 3. Decidir qué función llamar según el rol
if ($role === 'admin') {
    // --- ES ADMIN ---
    $account = $_POST['accountNumber'] ?? '';
    
    // Llamamos a modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    // Nota: Asegúrate de que el orden de parámetros coincide con tu Controller.php
    $result = $controller->modifyAdmin($email, $username, $phone, $name, $surname, $account, $targetId);

} else {
    // --- ES USUARIO ---
    $card = $_POST['cardNumber'] ?? '';
    $gender = $_POST['gender'] ?? 'Other';
    
    // Llamamos a modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code)
    $result = $controller->modifyUser($email, $username, $phone, $name, $surname, $gender, $card, $targetId);
}

// 4. Respuesta
if ($result) {
    echo json_encode(['exito' => true]);
} else {
    echo json_encode(['exito' => false, 'error' => 'Error al actualizar la base de datos']);
}
?>