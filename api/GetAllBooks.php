<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../controller/BookController.php';

$controller = new BookController();
$libros = $controller->getAllBooks(); // Llamamos a la función que acabamos de crear

if ($libros) {
    echo json_encode(["exito" => true, "libros" => $libros]);
} else {
    // Si no hay libros o hay error, devolvemos array vacío pero exito true (no es un error fatal)
    echo json_encode(["exito" => true, "libros" => []]);
}
