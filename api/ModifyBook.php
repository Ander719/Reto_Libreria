<?php
// Edicion de libros desde el panel admin. Si llega portada nueva, sustituye la anterior.
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/BookController.php';
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Acceso restringido a administradores.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
$oldCover = $_POST['cover'] ?? '';

$isbn = trim(htmlspecialchars($isbn));
$title = trim(htmlspecialchars($title));
$authorName = trim(htmlspecialchars($authorName));
$authorSurname = trim(htmlspecialchars($authorSurname));
$synopsis = trim(htmlspecialchars($synopsis));
$editorial = trim(htmlspecialchars($editorial));
$oldCover = trim(htmlspecialchars($oldCover));

$pages = filter_var($pages, FILTER_VALIDATE_INT);
$stock = filter_var($stock, FILTER_VALIDATE_INT);
$price = filter_var($price, FILTER_VALIDATE_FLOAT);

if (!preg_match('/^\d{13}$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El ISBN debe tener 13 dígitos.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($isbn) || empty($title) || empty($authorName) || $pages === false || $stock === false || $price === false) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de entrada no válidos.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$finalCoverName = $oldCover;
if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['coverFile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Error al subir el archivo',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    // La portada anterior se conserva si no hay archivo nuevo y se elimina si se reemplaza.
    $maxSize = 2 * 1024 * 1024;
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $fileSize = $_FILES['coverFile']['size'] ?? 0;
    $tmpPath = $_FILES['coverFile']['tmp_name'] ?? '';
    $originalName = $_FILES['coverFile']['name'] ?? '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;

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
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $uploadDir = '../view/assets/img/covers/';
    $newFileName = uniqid('cover_', true) . '.' . $extension;

    if (move_uploaded_file($tmpPath, $uploadDir . $newFileName)) {
        $finalCoverName = $newFileName;
        if ($oldCover && file_exists($uploadDir . $oldCover)) {
            @unlink($uploadDir . $oldCover);
        }
    }
}

$controller = new BookController();
$result = $controller->modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $finalCoverName);

if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Libro actualizado correctamente.',
        'data' => [
            'updated' => true,
            'isbn' => $isbn,
            'cover' => $finalCoverName
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al actualizar el libro en la base de datos.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
