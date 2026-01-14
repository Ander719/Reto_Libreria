<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

$isbn = $_GET['isbn'] ?? '';

if (!$isbn) {
    echo json_encode(["error" => "No se proporcionó ISBN"]);
    exit;
}

$controller = new controller();
$book = $controller->getBook($isbn);

// 3. Devolvemos el resultado
if ($book) {
    echo json_encode(["exito" => true, "libro" => $book]);
} else {
    echo json_encode(["exito" => false, "error" => "Libro no encontrado"]);
}
?>
