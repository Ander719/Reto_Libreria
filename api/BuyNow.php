<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/OrderController.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['profileCode']) || empty($input['isbn']) || empty($input['quantity'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos incompletos.',
        'data' => null
    ]);
    exit;
}

$profileCode = filter_var($input['profileCode'], FILTER_VALIDATE_INT);
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
