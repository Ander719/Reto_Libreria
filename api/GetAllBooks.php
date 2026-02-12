<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/BookController.php';

$controller = new BookController();
$books = $controller->getAllBooks();

    if ($books) {
        http_response_code(200);
        echo json_encode(["success" => true, "books" => $books]);
    } else {
        http_response_code(200);
        echo json_encode(["success" => true, "books" => []]);
    }
?>