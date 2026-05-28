<?php
require_once '../Config/Database.php';
require_once '../model/dao/OrderDao.php';

// Punto de entrada de la API para pedidos
class OrderController {
    private $orderDao;

    // Prepara el DAO de pedidos
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->orderDao = new OrderDao($db);
    }
    // Devuelve el historial de pedidos de un perfil
    public function getOrdersByProfile($profileCode) {
        return $this->orderDao->getOrdersByProfile($profileCode);
    }

    // Lanza una compra directa de un libro
    public function createDirectOrder($profileCode, $isbn, $quantity) {
        return $this->orderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>
