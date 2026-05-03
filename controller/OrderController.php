<?php
require_once '../Config/Database.php';
require_once '../model/dao/OrderDao.php';

class OrderController {
    private $orderDao;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->orderDao = new OrderDao($db);
    }
    public function getOrdersByProfile($profileCode) {
        return $this->orderDao->getOrdersByProfile($profileCode);
    }

    public function createDirectOrder($profileCode, $isbn, $quantity) {
        return $this->orderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>
