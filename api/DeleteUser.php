<?php
require_once '../Config/Session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

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

$data = json_decode(file_get_contents("php://input"), true);
$idToDelete = $data['id'] ?? null;

    if (!$idToDelete) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No ID provided']);
        exit;
    }
$isSelfDelete = (isset($_SESSION['user']['profile_code']) && $_SESSION['user']['profile_code'] == $idToDelete);
$controller = new ProfileController();
$result = $controller->delete_user($idToDelete);

    if ($result) {
        if ($isSelfDelete) {
            session_destroy();
        }
        http_response_code(200);
        echo json_encode(['success' => true, 'isSelfDelete' => $isSelfDelete]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al eliminar en la BD']);
    }
?>
