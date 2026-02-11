<?php
include_once '../Config/Database.php';
include_once '../model/dao/OrderDao.php';
include_once '../Config/Session.php'; 

header("Content-Type: application/json");
$orderDao = new OrderDao();
$profileCode = $_GET['profileCode'] ?? null;

if ($profileCode) {
    $orders = $orderDao->getOrdersByProfile($profileCode);
    http_response_code(200);
    echo json_encode($orders);
} else {
    http_response_code(500);
    echo json_encode(["error" => "No profile code provided"]);
}