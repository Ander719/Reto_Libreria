<?php
// api/AddBook.php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'error' => 'Método no permitido']);
    exit;
}

$isbn = $_POST['isbn'] ?? '';
$title = $_POST['title'] ?? '';
$authorName = $_POST['authorName'] ?? '';
$authorSurname = $_POST['authorSurname'] ?? '';
$pages = $_POST['pages'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$synopsis = $_POST['synopsis'] ?? '';
$price = $_POST['price'] ?? 0;
$editorial = $_POST['editorial'] ?? '';
$coverName = "default.jpg"; 

if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../view/assets/img/covers/';
    $extension = strtolower(pathinfo($_FILES['coverFile']['name'], PATHINFO_EXTENSION));
    $newFileName = 'cover_' . time() . '_' . rand(100, 999) . '.' . $extension;
    
    if (move_uploaded_file($_FILES['coverFile']['tmp_name'], $uploadDir . $newFileName)) {
        $coverName = $newFileName;
    }
}

$controller = new BookController();
$response = $controller->createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);

echo json_encode(['exito' => $response, 'message' => $response ? 'Libro creado' : 'Error al guardar']);
?>