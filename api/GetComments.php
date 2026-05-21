<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../controller/CommentController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$controller = new CommentController();
$isbn = isset($_GET['isbn']) ? trim(htmlspecialchars($_GET['isbn'])) : "";

if ($isbn) {
    if (!preg_match('/^\d{13}$/', $isbn)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'ISBN no válido.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $comments = $controller->getCommentsByISBN($isbn);
    $data = array_map(function ($commentEntity) {
        return $commentEntity->toArray();
    }, $comments);
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Comentarios obtenidos correctamente.',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ISBN no proporcionado.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
