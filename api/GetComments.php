<?php
// api/GetComments.php — Endpoint para obtener los comentarios de un libro por ISBN
// Requisito profesor: códigos HTTP correctos, respuestas JSON estandarizadas

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/CommentController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para consultar comentarios.',
        'data' => null
    ]);
    exit;
}

// 4. Recogida del parámetro ISBN de forma segura
$isbnRaw = filter_input(INPUT_GET, 'isbn', FILTER_SANITIZE_STRING);
$isbn = isset($isbnRaw) ? htmlspecialchars(trim($isbnRaw), ENT_QUOTES, 'UTF-8') : '';

// 5. Validación server-side: ISBN obligatorio
if (empty($isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Se requiere un ISBN válido para consultar los comentarios.',
        'data' => null
    ]);
    exit;
}

// 6. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new CommentController($db);

// 7. Obtención de los comentarios del libro
$comments = $controller->getCommentsByISBN($isbn);

// 8. Respuesta de éxito con código 200
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Comentarios obtenidos correctamente.',
    'data' => ['comments' => $comments ?: []]
]);
?>
