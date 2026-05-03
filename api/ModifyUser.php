<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'code' => 401, 'message' => 'No has iniciado sesión', 'data' => null]);
        exit;
    }

$userSession = $_SESSION['user'];
$loggedUserId = $userSession['profile_code'];
$isAdmin = ($userSession['role'] === 'admin');
$targetId = $_POST['target_id'] ?? $loggedUserId;
$roleForm = $_POST['role'] ?? 'user';

    if ($targetId != $loggedUserId && !$isAdmin) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'code' => 403, 'message' => 'Sin permisos', 'data' => null]);
        exit;
    }

$telephone = trim($_POST['phone'] ?? '');
$name = trim(htmlspecialchars($_POST['name'] ?? ''));
$surname = trim(htmlspecialchars($_POST['surname'] ?? ''));
$email = trim($_POST['email'] ?? '');
$username = trim(htmlspecialchars($_POST['username'] ?? ''));
$targetId = filter_var($targetId, FILTER_VALIDATE_INT);
$telephone = $telephone !== '' ? filter_var($telephone, FILTER_SANITIZE_NUMBER_INT) : '';

if (!empty($telephone)) {
    if (strlen($telephone) !== 9 || !is_numeric($telephone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'Teléfono inválido (debe tener 9 dígitos).', 'data' => null]);
        exit;
    }
}

if ($targetId === false || empty($username) || empty($name) || empty($surname)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'Datos obligatorios inválidos.', 'data' => null]);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'Formato de email inválido.', 'data' => null]);
    exit;
}

$controller = new ProfileController();
$result = false;

if ($roleForm === 'admin') {
    $acc = trim($_POST['accountNumber'] ?? '');
    if (!empty($acc)) {
        if (strlen($acc) !== 24) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'Cuenta bancaria inválida (24 caracteres).', 'data' => null]);
            exit;
        }
    }
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $acc, $targetId);
} else {
    $card = trim($_POST['cardNumber'] ?? '');
    if (!empty($card)) {
        if (strlen($card) !== 16 || !is_numeric($card)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'Tarjeta inválida (debe tener 16 dígitos).', 'data' => null]);
            exit;
        }
    }
    $gender = htmlspecialchars($_POST['gender'] ?? 'Other');
    $direction = trim(htmlspecialchars($_POST['direction'] ?? ''));
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card, $targetId,$direction);
}

if ($result) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'code' => 200, 'message' => 'Usuario actualizado correctamente', 'data' => null]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => 'Error en BD', 'data' => null]);
}
?>
