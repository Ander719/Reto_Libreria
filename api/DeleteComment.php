<?php
// Borrado de resenas: el usuario borra la suya; un admin puede moderar las de otros.
header('Content-Type: application/json; charset=utf-8');

require_once '../controller/CommentController.php';
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$commentController = new CommentController();

if (!is_object($data)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!empty($data->isbn)) {
    $isbn = trim(htmlspecialchars((string)$data->isbn));

    if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'code' => 401,
            'message' => 'Debes iniciar sesión.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $sessionProfileCode = filter_var($_SESSION['user']['profile_code'], FILTER_VALIDATE_INT);
    $targetProfileCode = filter_var($data->profileCode ?? null, FILTER_VALIDATE_INT);
    $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    $profileCode = $isAdmin && $targetProfileCode !== false ? $targetProfileCode : $sessionProfileCode;

    if ($isbn === '' || $profileCode === false || $profileCode <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Faltan datos.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $deleted = $commentController->deleteComment($isbn, $profileCode);
    if ($deleted) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => 'Comentario eliminado.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'code' => 500,
            'message' => 'Error en BBDD al eliminar.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan datos.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
