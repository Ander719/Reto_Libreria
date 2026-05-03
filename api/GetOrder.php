<?php
require_once '../controller/OrderController.php';
require_once '../Config/Session.php';

header("Content-Type: application/json; charset=utf-8");

// 1. Verificación de si usuario ha iniciado la sesion
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No has iniciado sesión.',
        'data' => null
    ]);
    exit();
}

$profileCode = $_SESSION['user']['profile_code'];
$profileCode = filter_var($profileCode, FILTER_VALIDATE_INT);

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

try {
    $orderController = new OrderController();

    $orders = $orderController->getOrdersByProfile($profileCode);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Pedidos obtenidos correctamente.',
        'data' => $orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al obtener pedidos.',
        'data' => null
    ]);
}
