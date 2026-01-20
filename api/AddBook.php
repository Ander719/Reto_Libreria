<?php
// api/AddBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// --- CAMBIO PRINCIPAL: Usamos el controlador específico de Libros ---
require_once '../controller/BookController.php';

// 1. VALIDACIÓN DE MÉTODO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'error' => 'Método no permitido. Se requiere POST.']);
    exit;
}

// 2. RECOGIDA DE DATOS
$isbn = $_POST['isbn'] ?? '';
$title = $_POST['title'] ?? '';
$authorName = $_POST['authorName'] ?? '';
$authorSurname = $_POST['authorSurname'] ?? '';
$pages = $_POST['pages'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$synopsis = $_POST['synopsis'] ?? '';
$price = $_POST['price'] ?? 0;
$editorial = $_POST['editorial'] ?? '';

// 3. VALIDACIÓN DE CAMPOS OBLIGATORIOS
if (empty($isbn) || empty($title) || empty($authorName)) {
    echo json_encode(['exito' => false, 'error' => 'Error: ISBN, Título y Nombre de Autor son obligatorios.']);
    exit;
}

// 4. GESTIÓN DE LA IMAGEN
$coverName = "default.jpg"; 

if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['coverFile'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowed_extensions)) {
        $uploadDir = __DIR__ . '/../view/assets/img/covers/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = 'cover_' . time() . '_' . rand(100, 999) . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $coverName = $newFileName;
        } else {
            echo json_encode(['exito' => false, 'error' => 'Error al mover el archivo subido.']);
            exit;
        }
    } else {
        echo json_encode(['exito' => false, 'error' => 'Formato de imagen no permitido.']);
        exit;
    }
}

// 5. LLAMADA AL CONTROLADOR (CORREGIDO)
// Usamos BookController en lugar de 'controller'
$controller = new BookController();

$response = $controller->createBook(
    $isbn, 
    $title, 
    $authorName, 
    $authorSurname, 
    $pages, 
    $stock, 
    $synopsis, 
    $price, 
    $editorial, 
    $coverName
);

// 6. RESPUESTA
if ($response) {
    echo json_encode(['exito' => true, 'message' => 'Libro creado correctamente.']);
} else {
    // Si falla, suele ser por ISBN duplicado o error en BD
    echo json_encode(['exito' => false, 'error' => 'Error al guardar. Verifica que el ISBN no exista ya.']);
}
?>