<?php
// Sustituimos el DAO por el Controlador
require_once '../controller/OrderController.php';
require_once '../Config/Session.php'; 

header("Content-Type: application/json; charset=utf-8");

// 1. Verificación de sesión
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit();
}

$profileCode = $_SESSION['user']['profile_code'];

try {
    // 2. Usar el Controlador en lugar del DAO
    $orderController = new OrderController();
    
    // 3. Llamar al nuevo método del controlador que incluye validación
    $orders = $orderController->getOrdersByProfile($profileCode);

    http_response_code(200);
    echo json_encode($orders);

} catch (Exception $e) {
    http_response_code(500);
    // Es mejor no mostrar el mensaje exacto de la excepción en producción
    echo json_encode(["success" => false, "error" => "Error al obtener pedidos."]);
}