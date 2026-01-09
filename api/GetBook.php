<?php
// api/GetBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

$isbn = $_GET['isbn'] ?? '';

$controller = new controller();
$book = $controller->getBook($isbn);

if ($book) {
    // Convertimos el objeto Book a un array para el JSON
    $bookData = [
        "isbn" => $book->getIsbn(),
        "title" => $book->getTitle(),
        "author" => $book->getAuthor(),
        "pages" => $book->getPages(),
        "stock" => $book->getStock(),
        "synopsis" => $book->getSynopsis(),
        "price" => $book->getPrice(),
        "editorial" => $book->getEditorial(),
        "cover" => $book->getCover()
    ];
    echo json_encode(['exito' => true, 'resultado' => $bookData], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['exito' => false, 'error' => 'Libro no encontrado']);
}
?>