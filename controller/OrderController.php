<?php
require_once '../Config/Database.php';
require_once '../model/dao/OrderDao.php';

class OrderController {
    private $OrderDao;

    public function __construct() {
        $this->OrderDao = new OrderDao();
    }

    public function createDirectOrder($profileCode, $isbn, $quantity) {
        return $this->OrderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>