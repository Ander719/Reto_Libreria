<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';
require_once '../model/entities/Comment.php';
require_once '../Config/Session.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$controller = new CommentController();

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

if (empty($data->isbn) || empty($data->text) || !isset($data->rating)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan datos para actualizar.',
        'data' => null
    ]);
    exit;
}

$profileCode = (string) $_SESSION['user']['profile_code'];
$isbn = trim(htmlspecialchars((string)$data->isbn));
$text = trim(htmlspecialchars((string)$data->text));
$rating = filter_var($data->rating, FILTER_VALIDATE_FLOAT);

if ($profileCode === '' || $isbn === '' || $text === '' || $rating === false || $rating < 0 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de comentario no válidos.',
        'data' => null
    ]);
    exit;
}

$comment = new Comment();
$comment->setProfileCode($profileCode);
$comment->setIsbn($isbn);
$comment->setCommentText($text);
$comment->setRating($rating);

$updated = $controller->updateComment($comment);

http_response_code($updated ? 200 : 503);
echo json_encode([
    'status' => $updated ? 'success' : 'error',
    'code' => $updated ? 200 : 503,
    'message' => $updated ? 'Comentario actualizado.' : 'No se pudo actualizar o no hubo cambios.',
    'data' => null
]);
?>
