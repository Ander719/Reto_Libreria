<?php
// api/GetAllUsers.php — Endpoint para obtener el listado de todos los usuarios (solo admin)
// Requisito profesor: gestión de sesiones, códigos HTTP correctos

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/ProfileController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para consultar usuarios.',
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

// 5. Verificación de rol administrador: solo admins pueden ver la lista completa
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Acceso restringido. Solo los administradores pueden ver la lista de usuarios.',
        'data' => null
    ]);
    exit;
}

// 6. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 7. Obtención del listado completo de usuarios
$users = $controller->get_all_users();

// 8. Respuesta de éxito con código 200
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Listado de usuarios obtenido exitosamente.',
    'data' => ['users' => $users ?: []]
]);
?>
