<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/BookController.php';

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
