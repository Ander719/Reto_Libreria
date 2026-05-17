<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once '../controller/CommentController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => []
    ]);
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
            'data' => []
        ]);
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
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ISBN no proporcionado.',
        'data' => []
    ]);
}
?>
