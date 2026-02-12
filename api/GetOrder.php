<?php
include_once '../model/dao/OrderDao.php';
include_once '../Config/Session.php'; 

header("Content-Type: application/json");

if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit();
}

// 2. Obtener ID del usuario logueado
$profileCode = $_SESSION['user']['profile_code'];

// 3. Obtener Pedidos (Ya estructurados por OrderDao)
try {
    $orderDao = new OrderDao();
    $orders = $orderDao->getOrdersByProfile($profileCode);

    http_response_code(200);
    echo json_encode($orders);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error al obtener pedidos: " . $e->getMessage()]);
}