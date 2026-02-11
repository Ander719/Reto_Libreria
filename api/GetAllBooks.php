<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/BookController.php';

$controller = new BookController();
$books = $controller->getAllBooks();

if ($books) {
    echo json_encode(["success" => true, "books" => $books]);
} else {
    echo json_encode(["success" => true, "books" => []]);
}
?>