<?php
// api/AddComment.php — Endpoint para añadir un comentario a un libro
// Requisito profesor: validaciones server-side, gestión de sesiones

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller, Entity
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/CommentController.php';
require_once '../model/entities/Comment.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para añadir un comentario.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa: solo usuarios autenticados pueden comentar
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Inicia sesión para poder publicar un comentario.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida de datos: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$input = $jsonInput ?: $_POST;

$isbn = isset($input['isbn']) ? trim($input['isbn']) : '';
$text = isset($input['text']) ? trim($input['text']) : '';
$rating = isset($input['rating']) ? (int)$input['rating'] : 0;

// 6. Saneamiento del texto del comentario para prevenir XSS
$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

// 7. Validación server-side: campos obligatorios y rating en rango 1-5
if (empty($isbn) || empty($text) || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos o valoración fuera de rango (1-5).',
        'data' => null
    ]);
    exit;
}

// 8. Obtención del profile_code de la sesión activa
$profileCode = $_SESSION['user']['profile_code'];

// 9. Creación del objeto Comment con los datos validados
$comment = new Comment();
$comment->setProfileCode($profileCode);
$comment->setIsbn($isbn);
$comment->setCommentText($text);
$comment->setRating($rating);
$comment->setDateComment(date('Y-m-d'));

// 10. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new CommentController($db);

// 11. Guardado del comentario en la base de datos
$result = $controller->addComment($comment);

// 12. Respuesta según resultado
if ($result) {
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'code' => 201,
        'message' => 'Comentario publicado con éxito.',
        'data' => ['isbn' => $isbn]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al guardar el comentario en la base de datos.',
        'data' => null
    ]);
}
?>
