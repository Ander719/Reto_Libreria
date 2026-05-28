<?php
// Cambio de contrasena del perfil conectado.
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/ProfileController.php';
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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$password = $input['password'] ?? '';
$targetProfileCode = $input['profile_code'] ?? null;

if (empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Si es admin y envia un profile_code distinto, cambia la pass del target.
// Si no, solo permite cambiar la propia.
$sessionCode = (string) $_SESSION['user']['profile_code'];
$isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';

if ($isAdmin && $targetProfileCode !== null) {
    $profile_code = (string) $targetProfileCode;
} else {
    $profile_code = $sessionCode;
}

$password = trim($password);

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$controller = new ProfileController();
$modify = $controller->modifyPassword($profile_code, $passwordHash);

if ($modify) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Contraseña modificada correctamente',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al modificar la contraseña',
        'data' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
