<?php
// api/BuyNow.php — Endpoint para procesar la compra de un libro
// Requisito profesor: validaciones server-side, gestión de sesiones, códigos HTTP

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/OrderController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para realizar una compra.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa: solo usuarios autenticados pueden comprar
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado. Debes iniciar sesión para comprar.',
        'data' => null
    ]);
    exit;
}

// 5. Recogida de datos del cuerpo JSON
$jsonInput = json_decode(file_get_contents('php://input'), true);

if (!is_array($jsonInput)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'No se recibieron datos de transacción válidos.',
        'data' => null
    ]);
    exit;
}

$profileCode = isset($jsonInput['profileCode']) ? (int)$jsonInput['profileCode'] : 0;
$isbn = isset($jsonInput['isbn']) ? htmlspecialchars(trim($jsonInput['isbn']), ENT_QUOTES, 'UTF-8') : '';
$quantity = isset($jsonInput['quantity']) ? (int)$jsonInput['quantity'] : 0;

// 6. Validación de formato ISBN (13 dígitos numéricos)
if (!empty($isbn) && !preg_match('/^\d{13}$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El ISBN debe contener exactamente 13 dígitos numéricos.',
        'data' => null
    ]);
    exit;
}

// 7. Verificación anti-suplantación: el profileCode debe coincidir con el de sesión
$loggedProfileCode = $_SESSION['user']['profile_code'];
if ($profileCode != $loggedProfileCode) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Permisos insuficientes. No puedes realizar compras para otro perfil.',
        'data' => null
    ]);
    exit;
}

// 8. Validación server-side: parámetros obligatorios y rangos
if (empty($isbn) || $quantity <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Se requiere un ISBN válido y una cantidad superior a cero.',
        'data' => null
    ]);
    exit;
}

// Límite máximo de compra por seguridad (evitar abuso)
if ($quantity > 99) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'La cantidad máxima permitida por compra es de 99 unidades.',
        'data' => null
    ]);
    exit;
}

// 9. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new OrderController($db);

// 10. Procesamiento de la compra
$result = $controller->createDirectOrder($loggedProfileCode, $isbn, $quantity);

// 11. Respuesta según resultado
if ($result === true) {
    // Compra exitosa: código 201 (Created)
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'code' => 201,
        'message' => '¡Compra finalizada! El pedido ha sido registrado correctamente.',
        'data' => ['isbn' => $isbn, 'quantity' => $quantity]
    ]);
} elseif ($result === 'NO_STOCK') {
    // Stock insuficiente: código 409 (Conflict)
    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'code' => 409,
        'message' => 'Stock insuficiente para completar esta compra.',
        'data' => null
    ]);
} else {
    // Error interno: código 500
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Fallo en la transacción al procesar el pedido.',
        'data' => null
    ]);
}
?>
