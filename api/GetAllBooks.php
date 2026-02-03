<?php
// api/GetAllBooks.php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

$controller = new BookController();
$libros = $controller->getAllBooks();

// Devolvemos la lista directamente para el JS
echo json_encode($libros);
?>