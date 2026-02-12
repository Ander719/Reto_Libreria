<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

$isbn = $_GET['isbn'] ?? '';

    if (empty($isbn)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'error' => 'ISBN no proporcionado']);
        exit;
    }

$controller = new BookController();
$libro = $controller->getBook($isbn);

    if ($libro) {
        http_response_code(200);
        echo json_encode(['exito' => true, 'libro' => $libro]);
    } else {
        http_response_code(404);
        echo json_encode(['exito' => false, 'error' => 'Libro no encontrado']);
    }
?>