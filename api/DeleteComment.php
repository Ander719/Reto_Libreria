<?php
// api/DeleteComment.php — Endpoint para eliminar un comentario
// Requisito profesor: gestión de sesiones, validaciones server-side, autorización

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/CommentController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para eliminar un comentario.',
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
        'message' => 'Autenticación requerida para eliminar comentarios.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida de datos: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$input = $jsonInput ?: $_POST;

$isbn = isset($input['isbn']) ? trim($input['isbn']) : '';
$profileCode = isset($input['profileCode']) ? (int)$input['profileCode'] : 0;

// 6. Verificación de permisos: autor del comentario o administrador
$loggedId = $_SESSION['user']['profile_code'];
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

if ($loggedId != $profileCode && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No posees los privilegios necesarios para borrar este comentario.',
        'data' => null
    ]);
    exit;
}

// 7. Validación server-side: campos obligatorios
if (empty($isbn) || $profileCode <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Parámetros de eliminación insuficientes.',
        'data' => null
    ]);
    exit;
}

// 8. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new CommentController($db);

// 9. Eliminación del comentario en la base de datos
$result = $controller->deleteComment($isbn, $profileCode);

// 10. Respuesta según resultado
if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Comentario eliminado exitosamente.',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error interno al intentar borrar la reseña.',
        'data' => null
    ]);
}
?>
