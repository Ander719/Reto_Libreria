<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';
require_once '../model/entities/Comment.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$controller = new CommentController();

if (empty($data->profileCode) || empty($data->isbn) || empty($data->text) || !isset($data->rating)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos.',
        'data' => null
    ]);
    exit;
}

$profileCode = trim(htmlspecialchars((string)$data->profileCode));
$isbn = trim(htmlspecialchars((string)$data->isbn));
$text = trim(htmlspecialchars((string)$data->text));
$rating = filter_var($data->rating, FILTER_VALIDATE_FLOAT);
$date = !empty($data->date) ? trim(htmlspecialchars((string)$data->date)) : date('Y-m-d');

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
$comment->setDateComment($date);

$created = $controller->addComment($comment);

http_response_code($created ? 201 : 503);
echo json_encode([
    'status' => $created ? 'success' : 'error',
    'code' => $created ? 201 : 503,
    'message' => $created ? 'Comentario publicado correctamente.' : 'Error al guardar el comentario.',
    'data' => null
]);
?>
