<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';
require_once '../controller/ProfileController.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$commentController = new CommentController();
$profileController = new ProfileController();

if (!empty($data->isbn) && !empty($data->profileCode)) {
    $isbn = trim(htmlspecialchars((string)$data->isbn));
    $profileCode = trim(htmlspecialchars((string)$data->profileCode));

    if ($isbn === '' || $profileCode === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Faltan datos.',
            'data' => null
        ]);
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'code' => 401,
            'message' => 'Debes iniciar sesión.',
            'data' => null
        ]);
        exit;
    }

    $loggedProfileCode = (string) $_SESSION['user']['profile_code'];
    $isOwner = $loggedProfileCode === $profileCode;
    $isAdmin = $profileController->isAdminByProfileCode($loggedProfileCode);

    if (!$isOwner && !$isAdmin) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'code' => 403,
            'message' => 'No tienes permisos para borrar este comentario.',
            'data' => null
        ]);
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
        ]);
    } else {
        http_response_code(503);
        echo json_encode([
            'status' => 'error',
            'code' => 503,
            'message' => 'Error en BBDD al eliminar.',
            'data' => null
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan datos.',
        'data' => null
    ]);
}
?>
