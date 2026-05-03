<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Método no permitido',
        'data' => null
    ]);
    exit;
}

$profile_code = $_POST['profile_code'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$current_account = $_POST['current_account'] ?? '';

$profile_code = trim(htmlspecialchars($profile_code));
$email = trim($email);
$username = trim(htmlspecialchars($username));
$telephone = trim($telephone);
$name = trim(htmlspecialchars($name));
$surname = trim(htmlspecialchars($surname));
$current_account = trim($current_account);

if (empty($profile_code) || empty($email) || empty($username)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan parámetros obligatorios',
        'data' => null
    ]);
    exit;
}

$controller = new ProfileController();
$modify = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);

if ($modify) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Admin modificado correctamente',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al modificar el admin',
        'data' => null
    ]);
}
?>
