<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

$isbn = $_GET['isbn'] ?? '';

if (empty($isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ISBN no proporcionado',
        'data' => null
    ]);
    exit;
}

$controller = new BookController();
$libro = $controller->getBook($isbn);

if ($libro) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Libro encontrado',
        'data' => $libro
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'code' => 404,
        'message' => 'Libro no encontrado',
        'data' => null
    ]);
}
?>
