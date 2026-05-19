<?php
// api/GetBook.php — Endpoint para obtener un libro específico por su ISBN
// Requisito profesor: validaciones server-side, códigos HTTP correctos

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/BookController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para consultar un libro.',
        'data' => null
    ]);
    exit;
}

// 4. Recogida del parámetro ISBN de forma segura usando filter_input
//    Esto previene inyección de código malicioso a través de la URL
$isbnRaw = filter_input(INPUT_GET, 'isbn', FILTER_SANITIZE_STRING);
$isbn = isset($isbnRaw) ? htmlspecialchars(trim($isbnRaw), ENT_QUOTES, 'UTF-8') : '';

// 5. Validación server-side: ISBN obligatorio
if (empty($isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El ISBN es obligatorio para buscar un libro.',
        'data' => null
    ]);
    exit;
}

// 6. Validación de formato: ISBN debe tener exactamente 13 dígitos numéricos
if (!preg_match('/^\d{13}$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El ISBN debe contener exactamente 13 dígitos numéricos.',
        'data' => null
    ]);
    exit;
}

// 7. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new BookController($db);

// 8. Búsqueda del libro por ISBN
$book = $controller->getBook($isbn);

// 9. Respuesta según resultado
if ($book) {
    // Libro encontrado: código 200
    $bookData = is_object($book) && method_exists($book, 'toArray') ? $book->toArray() : $book;

    // Añadimos los campos del autor de forma plana para compatibilidad con el frontend
    if (isset($bookData['author']) && is_array($bookData['author'])) {
        $bookData['name_author'] = $bookData['author']['name'] ?? '';
        $bookData['last_name'] = $bookData['author']['lastname'] ?? $bookData['author']['last_name'] ?? '';
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Libro encontrado.',
        'data' => ['book' => $bookData]
    ]);
} else {
    // Libro no encontrado: código 404
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'code' => 404,
        'message' => 'El libro solicitado no existe en nuestra base de datos.',
        'data' => null
    ]);
}
?>
