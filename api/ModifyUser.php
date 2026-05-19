<?php
// api/ModifyUser.php — Endpoint para modificar datos de un perfil (propio o admin)
// Requisito profesor: validaciones server-side, gestión de sesiones

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
        'message' => 'Método no permitido. Use POST para modificar datos.',
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

// 5. Determinación del perfil objetivo y verificación de permisos
$loggedUserId = $_SESSION['user']['profile_code'];
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');
$targetId = isset($_POST['target_id']) ? (int)$_POST['target_id'] : $loggedUserId;
$role = isset($_POST['role']) ? trim($_POST['role']) : 'user';

// Solo puedes modificar tu propio perfil a menos que seas administrador
if ($targetId !== $loggedUserId && !$isAdmin) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'No tienes permisos para modificar este perfil.',
        'data' => null
    ]);
    exit;
}

// 6. Recogida y saneamiento de datos de entrada
$email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
$telephone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$surname = htmlspecialchars(trim($_POST['surname'] ?? ''), ENT_QUOTES, 'UTF-8');

// 7. Validación server-side: formato de email
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El formato del correo electrónico no es válido.',
        'data' => null
    ]);
    exit;
}

// 8. Validación server-side: formato de teléfono (9 dígitos)
if (!empty($telephone) && !preg_match('/^\d{9}$/', $telephone)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El teléfono debe contener exactamente 9 dígitos.',
        'data' => null
    ]);
    exit;
}

// 9. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 10. Modificación según el rol del perfil
if ($role === 'admin') {
    $account = htmlspecialchars(trim($_POST['accountNumber'] ?? ''), ENT_QUOTES, 'UTF-8');
    $result = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $account, $targetId);
} else {
    $gender = htmlspecialchars(trim($_POST['gender'] ?? 'Other'), ENT_QUOTES, 'UTF-8');
    $card = htmlspecialchars(trim($_POST['cardNumber'] ?? ''), ENT_QUOTES, 'UTF-8');
    $direction = htmlspecialchars(trim($_POST['direction'] ?? ''), ENT_QUOTES, 'UTF-8');
    $result = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card, $targetId, $direction);
}

// 11. Respuesta según resultado
if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Datos de perfil actualizados con éxito.',
        'data' => ['profile_code' => $targetId]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al intentar guardar los cambios en la base de datos.',
        'data' => null
    ]);
}
?>
