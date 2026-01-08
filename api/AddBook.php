<?php
// api/AddBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

// Recibir JSON
$input = json_decode(file_get_contents('php://input'), true);

// Extraer variables (o valores por defecto)
$isbn = $input['isbn'] ?? '';
$title = $input['title'] ?? '';
$author = $input['author'] ?? 0; // ID Autor
$pages = $input['pages'] ?? 0;
$stock = $input['stock'] ?? 0;
$synopsis = $input['synopsis'] ?? '';
$price = $input['price'] ?? 0;
$editorial = $input['editorial'] ?? '';
$cover = $input['cover'] ?? '';

$controller = new controller();
// Llamar a la nueva función en controller.php
$result = $controller->createBook($isbn, $title, $author, $pages, $stock, $synopsis, $price, $editorial, $cover);

if ($result) {
    echo json_encode(['exito' => true, 'message' => 'Libro creado correctamente'], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['exito' => false, 'error' => 'No se ha podido crear el libro']);
}
?>