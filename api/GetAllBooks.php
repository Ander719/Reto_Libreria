<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/BookController.php';

$controller = new BookController();
$books = $controller->getAllBooks(); // Llamamos a la función que acabamos de crear

if ($books) {
    echo json_encode(["success" => true, "books" => $books]);
} else {
    // Si no hay books o hay error, devolvemos array vacío pero success true (no es un error fatal)
    echo json_encode(["success" => true, "books" => []]);
}