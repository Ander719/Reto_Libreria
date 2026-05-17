<?php
require_once '../Config/Session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ]);
    exit;
}

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
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido.',
        'data' => null
    ]);
    exit;
}

$idToDelete = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

if ($idToDelete === false || $idToDelete <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'No se ha proporcionado un ID válido.',
        'data' => null
    ]);
    exit;
}

$sessionProfileCode = filter_var($_SESSION['user']['profile_code'], FILTER_VALIDATE_INT);
$isSelfDelete = ($sessionProfileCode !== false && $sessionProfileCode === $idToDelete);
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

if (!$isSelfDelete && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No tienes permisos para eliminar este usuario.',
        'data' => null
    ]);
    exit;
}

$controller = new ProfileController();
$result = $controller->delete_user($idToDelete);

if ($result) {
    if ($isSelfDelete) {
        session_destroy();
    }
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Usuario eliminado correctamente.',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al eliminar en la base de datos.',
        'data' => null
    ]);
}
?>
