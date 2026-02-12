<?php
require_once '../controller/OrderController.php';
require_once '../Config/Session.php';

header("Content-Type: application/json; charset=utf-8");

// 1. Verificación de si usuario ha iniciado la sesion
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {

    // Indica que el cliente debe autenticarse para obtener la respuesta.
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit();
}

$profileCode = $_SESSION['user']['profile_code'];

try {
    $orderController = new OrderController();

    $orders = $orderController->getOrdersByProfile($profileCode);

    // AÑADIDO HTTP: 200 OK. La solicitud ha tenido éxito.
    http_response_code(200);
    echo json_encode($orders);

} catch (Exception $e) {
    // Indica un fallo en el servidor
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error al obtener pedidos."]);
}