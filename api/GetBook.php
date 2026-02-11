<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

$isbn = $_GET['isbn'] ?? '';

if (empty($isbn)) {
    echo json_encode(['exito' => false, 'error' => 'ISBN no proporcionado']);
    exit;
}

$controller = new BookController();
$libro = $controller->getBook($isbn);

if ($libro) {
    echo json_encode(['exito' => true, 'libro' => $libro]);
} else {
    echo json_encode(['exito' => false, 'error' => 'Libro no encontrado']);
}
?>