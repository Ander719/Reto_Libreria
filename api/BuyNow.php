<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../controller/OrderController.php';
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ]);
    exit;
}

// Esta validación bloquea compras anónimas y exige identidad autenticada.
// Solo usamos el profile_code de sesión para impedir suplantación desde el payload.
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado.',
        'data' => null
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'JSON no válido.',
        'data' => null
    ]);
    exit;
}

if (empty($input['isbn']) || empty($input['quantity'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos.',
        'data' => null
    ]);
    exit;
}

$profileCode = filter_var($_SESSION['user']['profile_code'], FILTER_VALIDATE_INT);
$isbn = trim(htmlspecialchars($input['isbn']));
$quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT);

if ($profileCode === false || $quantity === false || empty($isbn) || $profileCode <= 0 || $quantity <= 0 || strlen($isbn) < 10) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de entrada no válidos.',
        'data' => null
    ]);
    exit;
}

$controller = new OrderController();
$result = $controller->createDirectOrder($profileCode, $isbn, $quantity);

if ($result === true) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Compra realizada con éxito.',
        'data' => null
    ]);
} elseif ($result === 'NO_STOCK') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'No hay suficiente stock.',
        'data' => null
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'No se pudo procesar la compra.',
        'data' => null
    ]);
}
?>
