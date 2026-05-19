<?php
// api/GetProfile.php — Endpoint para obtener el perfil de un usuario
// Requisito profesor: gestión de sesiones, validaciones server-side

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
        'message' => 'Método no permitido. Use GET para consultar un perfil.',
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

// 5. Recogida del ID objetivo: si no se proporciona, se usa el propio
$targetIdRaw = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$targetId = isset($targetIdRaw) ? (int)$targetIdRaw : $_SESSION['user']['profile_code'];

// 6. Verificación de permisos: solo puedes ver tu propio perfil o ser admin
$loggedUserId = $_SESSION['user']['profile_code'];
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

if ($targetId !== $loggedUserId && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No tienes permisos para consultar este perfil.',
        'data' => null
    ]);
    exit;
}

// 7. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 8. Búsqueda del perfil: primero admin, luego usuario estándar
$profile = $controller->getAdminById($targetId);
if (!$profile) {
    $profile = $controller->getUserById($targetId);
}

// 9. Respuesta según resultado
if ($profile) {
    // Convertimos a array y eliminamos el hash de la contraseña por seguridad
    $data = $profile->toArray();
    unset($data['pswd']);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Perfil recuperado correctamente.',
        'data' => $data
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'code' => 404,
        'message' => 'El usuario solicitado no existe en el sistema.',
        'data' => null
    ]);
}
?>
