<?php
// api/GetOrder.php — Endpoint para obtener el historial de compras del usuario
// Requisito profesor: gestión de sesiones, códigos HTTP correctos

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/OrderController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para consultar pedidos.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa con profile_code
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No has iniciado sesión.',
        'data' => null
    ]);
    exit;
}

// 5. Validación del profile_code como entero positivo
$profileCode = filter_var($_SESSION['user']['profile_code'], FILTER_VALIDATE_INT);
if ($profileCode === false || $profileCode <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Código de usuario no válido.',
        'data' => null
    ]);
    exit;
}

// 6. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new OrderController($db);

// 7. Obtención del historial de pedidos del usuario
$orders = $controller->getOrdersByProfile($profileCode);

// 8. Respuesta de éxito con código 200
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Historial de pedidos recuperado con éxito.',
    'data' => ['orders' => $orders ?: []]
]);
?>
