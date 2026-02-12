<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'No has iniciado sesión']);
        exit;
    }

$userSession = $_SESSION['user'];
$loggedUserId = $userSession['profile_code'];
$isAdmin = ($userSession['role'] === 'admin');
$targetId = $_POST['target_id'] ?? $loggedUserId;
$roleForm = $_POST['role'] ?? 'user';

    if ($targetId != $loggedUserId && !$isAdmin) {
        echo json_encode(['success' => false, 'error' => 'Sin permisos']);
        exit;
    }

$telephone = trim($_POST['phone'] ?? '');
$name = trim($_POST['name'] ?? '');
$surname = trim($_POST['surname'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');

if (!empty($telephone)) {
    if (strlen($telephone) !== 9 || !is_numeric($telephone)) {
        echo json_encode(['success' => false, 'error' => 'Teléfono inválido (debe tener 9 dígitos).']);
        exit;
    }
}

$controller = new ProfileController();
$result = false;

if ($roleForm === 'admin') {
    $acc = trim($_POST['accountNumber'] ?? '');
    if (!empty($acc)) {
        if (strlen($acc) !== 24) {
            echo json_encode(['success' => false, 'error' => 'Cuenta bancaria inválida (24 caracteres).']);
            exit;
        }
    }
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $acc, $targetId);
} else {
    $card = trim($_POST['cardNumber'] ?? '');
    if (!empty($card)) {
        if (strlen($card) !== 16 || !is_numeric($card)) {
            echo json_encode(['success' => false, 'error' => 'Tarjeta inválida (debe tener 16 dígitos).']);
            exit;
        }
    }
    $gender = $_POST['gender'] ?? 'Other';
    $direction = trim($_POST['direction'] ?? '');
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card, $targetId,$direction);
}

echo json_encode(['success' => $result, 'error' => $result ? null : 'Error en BD']);
?>