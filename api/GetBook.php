<?php
// api/GetBook.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

$isbn = $_GET['isbn'] ?? '';

if (!$isbn) {
    echo json_encode(["exito" => false, "error" => "No se proporcionó ISBN"]);
    exit;
}

<<<<<<< HEAD
try {
    $controller = new BookController();
    $book = $controller->getBook($isbn);
=======
$controller = new BookController();
$book = $controller->getBook($isbn);
>>>>>>> 78ce24ffba9cc8af46d53bc2deaf65dab1084b1e

    if ($book) {
        echo json_encode(["exito" => true, "libro" => $book]);
    } else {
        echo json_encode(["exito" => false, "error" => "Libro no encontrado"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["exito" => false, "error" => $e->getMessage()]);
}
?>