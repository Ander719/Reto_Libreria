<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

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

$controller = new BookController();
$books = $controller->getAllBooks();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Libros obtenidos correctamente',
    'data' => $books ?: []
]);
?>
