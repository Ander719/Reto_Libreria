<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
require_once '../Config/Session.php';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado.',
        'data' => null
    ]);
    exit;
}

if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Acceso restringido a administradores.',
        'data' => null
    ]);
    exit;
}

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

$profile_code = filter_var($_POST['profile_code'] ?? null, FILTER_VALIDATE_INT);
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$current_account = $_POST['current_account'] ?? '';

$email = trim($email);
$username = trim(htmlspecialchars($username));
$telephone = trim($telephone);
$name = trim(htmlspecialchars($name));
$surname = trim(htmlspecialchars($surname));
$current_account = trim($current_account);

if ($profile_code === false || $profile_code <= 0 || empty($email) || empty($username)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan parámetros obligatorios',
        'data' => null
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Email no válido',
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
