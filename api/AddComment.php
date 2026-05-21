<?php
// Guarda una resena del usuario conectado para el libro indicado.
header('Content-Type: application/json; charset=utf-8');

require_once '../controller/CommentController.php';
require_once '../model/entities/Comment.php';
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
$controller = new CommentController();

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

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($data->isbn) || empty($data->text) || !isset($data->rating)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$profileCode = (string) $_SESSION['user']['profile_code'];
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
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$comment = new Comment();
$comment->setProfileCode($profileCode);
$comment->setIsbn($isbn);
$comment->setCommentText($text);
$comment->setRating($rating);
$comment->setDateComment($date);

$created = $controller->addComment($comment);

http_response_code($created ? 201 : 500);
echo json_encode([
    'status' => $created ? 'success' : 'error',
    'code' => $created ? 201 : 500,
    'message' => $created ? 'Comentario publicado correctamente.' : 'Error al guardar el comentario.',
    'data' => null
]);
?>
