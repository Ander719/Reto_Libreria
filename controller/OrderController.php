<?php
// OrderController.php — Controlador para la gestión de pedidos y compras
// Actúa como un puente ciego entre la API y la capa de acceso a datos (DAO)
require_once '../model/dao/OrderDao.php';

class OrderController {
    private $OrderDao;

    public function __construct($db) {
        $this->OrderDao = new OrderDao($db);
    }
    
    // Solicita al DAO el historial de pedidos de un perfil específico
    public function getOrdersByProfile($profileCode) {
        return $this->OrderDao->getOrdersByProfile($profileCode);
    }

    // Delegamos la creación de un pedido directo para un libro al DAO
    public function createDirectOrder($profileCode, $isbn, $quantity) {
        return $this->OrderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>