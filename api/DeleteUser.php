<?php
// api/DeleteUser.php — Endpoint para eliminar una cuenta de usuario
// Requisito profesor: gestión de sesiones, validaciones server-side

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/ProfileController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para eliminar una cuenta.',
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
        'message' => 'No autorizado. Debes iniciar sesión.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida del ID a eliminar: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$idToDelete = isset($jsonInput['id']) ? (int)$jsonInput['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

// 6. Validación server-side: ID válido
if ($idToDelete <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'ID de usuario no válido.',
        'data' => null
    ]);
    exit;
}

// 7. Verificación de permisos: solo puedes eliminar tu propia cuenta o ser admin
$loggedUserId = $_SESSION['user']['profile_code'];
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

if ($idToDelete !== $loggedUserId && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No tienes permisos para eliminar este usuario.',
        'data' => null
    ]);
    exit;
}

// 8. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 9. Eliminación del usuario en la base de datos
$result = $controller->delete_user($idToDelete);

// 10. Respuesta según resultado
if ($result) {
    // Si el usuario se elimina a sí mismo, destruimos su sesión
    $isSelfDelete = ($idToDelete === $loggedUserId);
    if ($isSelfDelete) {
        // Vaciamos el array de sesión y destruimos la sesión del servidor
        $_SESSION = [];
        session_unset();
        session_destroy();
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Usuario eliminado del sistema correctamente.',
        'data' => ['isSelfDelete' => $isSelfDelete]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error interno al intentar eliminar el registro.',
        'data' => null
    ]);
}
?>
