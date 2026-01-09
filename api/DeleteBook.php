<?php
// api/DeleteBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../controller/controller.php';

$isbn = $_GET['isbn'] ?? '';

$controller = new controller();
$result = $controller->deleteBook($isbn);

if ($result) {
    echo json_encode(['result' => TRUE], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'No se pudo eliminar el libro']);
}
?>