<?php
// api/AddUser.php — Endpoint para registrar un nuevo usuario
// Requisito profesor: validaciones server-side, manejo claro de errores

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
        'message' => 'Método no permitido. Use POST para registrarse.',
        'data' => null
    ]);
    exit;
}

// 4. Recogida de datos: soportamos JSON y form-data
$jsonInput = json_decode(file_get_contents('php://input'), true);
$username = isset($jsonInput['username']) ? trim($jsonInput['username']) : (isset($_POST['username']) ? trim($_POST['username']) : '');
$password = isset($jsonInput['pswd1']) ? $jsonInput['pswd1'] : (isset($_POST['pswd1']) ? $_POST['pswd1'] : '');

// 5. Saneamiento del username para prevenir XSS
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

// 6. Validación server-side: campos obligatorios
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El nombre de usuario y la contraseña son obligatorios.',
        'data' => null
    ]);
    exit;
}

// 7. Validación de longitud mínima de contraseña
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'La contraseña debe tener al menos 8 caracteres.',
        'data' => null
    ]);
    exit;
}

// 8. Generación del hash de contraseña con password_hash (estándar seguro)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 9. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new ProfileController($db);

// 10. Registro del usuario en la base de datos
$response = $controller->register($username, $passwordHash);

// 11. Respuesta según resultado
if (is_object($response)) {
    // Registro exitoso: código 201 (Created)
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'code' => 201,
        'message' => 'Usuario registrado correctamente.',
        'data' => ['profile_code' => $response->getProfile_code()]
    ]);
} elseif ($response === 'ERROR_DUPLICADO') {
    // Usuario ya existe: código 400
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El nombre de usuario ya está en uso.',
        'data' => null
    ]);
} else {
    // Otro error: código 400
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Error al procesar el registro.',
        'data' => null
    ]);
}
?>
