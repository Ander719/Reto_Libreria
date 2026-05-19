<?php
// api/UpdateComment.php — Endpoint para editar un comentario existente
// Requisito profesor: validaciones server-side, gestión de sesiones, autorización

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
        'message' => 'Método no permitido. Use POST para editar un comentario.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'Inicia sesión para poder editar un comentario.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida de datos: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$input = $jsonInput ?: $_POST;

$isbn = isset($input['isbn']) ? trim($input['isbn']) : '';
$profileCode = isset($input['profileCode']) ? (int)$input['profileCode'] : 0;
$text = isset($input['text']) ? trim($input['text']) : '';
$rating = isset($input['rating']) ? (int)$input['rating'] : 0;

// 6. Saneamiento del texto
$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

// 7. Verificación de autoría: solo el dueño del comentario puede editarlo
$loggedUserId = $_SESSION['user']['profile_code'];
if ($profileCode !== $loggedUserId) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No tienes permisos para editar este comentario.',
        'data' => null
    ]);
    exit;
}

// 8. Validación server-side: campos obligatorios y rating en rango
if (empty($isbn) || $profileCode <= 0 || empty($text) || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de actualización inválidos o incompletos.',
        'data' => null
    ]);
    exit;
}

// 9. Creación del objeto Comment con los datos actualizados
$comment = new Comment();
$comment->setIsbn($isbn);
$comment->setProfileCode($profileCode);
$comment->setCommentText($text);
$comment->setRating($rating);

// 10. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new CommentController($db);

// 11. Actualización del comentario en la base de datos
$result = $controller->updateComment($comment);

// 12. Respuesta según resultado
if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Reseña actualizada correctamente.',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Fallo al procesar la actualización en la base de datos.',
        'data' => null
    ]);
}
?>
