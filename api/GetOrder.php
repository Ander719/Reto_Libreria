<?php
include_once '../Config/Database.php';
include_once '../model/dao/OrderDao.php';
include_once '../Config/Session.php'; // Para verificar usuario

header("Content-Type: application/json");
$orderDao = new OrderDao();
$profileCode = $_GET['profileCode'] ?? null;

if ($profileCode) {
    $orders = $orderDao->getOrdersByProfile($profileCode);
    echo json_encode($orders);
} else {
    http_response_code(400);
    echo json_encode(["error" => "No profile code provided"]);
}