<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Método no permitido',
        'data' => null
    ]);
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

$isbn = trim(htmlspecialchars($isbn));
$title = trim(htmlspecialchars($title));
$authorName = trim(htmlspecialchars($authorName));
$authorSurname = trim(htmlspecialchars($authorSurname));
$synopsis = trim(htmlspecialchars($synopsis));
$editorial = trim(htmlspecialchars($editorial));

$pages = filter_var($pages, FILTER_VALIDATE_INT);
$stock = filter_var($stock, FILTER_VALIDATE_INT);
$price = filter_var($price, FILTER_VALIDATE_FLOAT);

if (empty($isbn) || empty($title) || empty($authorName) || $pages === false || $stock === false || $price === false) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de entrada no válidos',
        'data' => null
    ]);
    exit;
}

if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $maxSize = 2 * 1024 * 1024;
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $fileSize = $_FILES['coverFile']['size'] ?? 0;
    $tmpPath = $_FILES['coverFile']['tmp_name'] ?? '';
    $originalName = $_FILES['coverFile']['name'] ?? '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (
        $fileSize <= 0 ||
        $fileSize > $maxSize ||
        !in_array($extension, $allowedExtensions, true) ||
        !in_array($realMimeType, $allowedMimeTypes, true)
    ) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Archivo no válido o muy grande',
            'data' => null
        ]);
        exit;
    }

    $uploadDir = '../view/assets/img/covers/';
    $newFileName = uniqid('cover_', true) . '.' . $extension;

    if (move_uploaded_file($tmpPath, $uploadDir . $newFileName)) {
        $coverName = $newFileName;
    }
}

$controller = new BookController();
$response = $controller->createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);

if ($response) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Libro creado',
        'data' => [
            'created' => true,
            'isbn' => $isbn,
            'cover' => $coverName
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al guardar',
        'data' => null
    ]);
}
?>
