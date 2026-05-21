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

$isbn = trim(htmlspecialchars($_GET['isbn'] ?? ''));

if (empty($isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ISBN no proporcionado',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!preg_match('/^\d{13}$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ISBN no válido',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$controller = new BookController();
$libro = $controller->getBook($isbn);

if ($libro) {
    $bookData = $libro->toArray();
    $bookData['name_author'] = $bookData['author']['name'] ?? '';
    $bookData['last_name'] = $bookData['author']['lastname'] ?? '';

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Libro encontrado',
        'data' => $bookData
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'code' => 404,
        'message' => 'Libro no encontrado',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
