<?php
require_once '../Config/Database.php';
require_once '../model/dao/OrderDao.php';

/**
 * Punto de entrada de la API para pedidos.
 */
class OrderController {
    private $orderDao;

    /**
     * Prepara el DAO de pedidos.
     */
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->orderDao = new OrderDao($db);
    }
    /**
     * Devuelve el historial de pedidos de un perfil.
     *
     * @param int $profileCode Codigo de perfil autenticado.
     * @return array<int, array<string, mixed>> Pedidos listos para JSON.
     */
    public function getOrdersByProfile($profileCode) {
        return $this->orderDao->getOrdersByProfile($profileCode);
    }

    /**
     * Lanza una compra directa de un solo libro.
     *
     * @param int $profileCode Codigo de perfil comprador.
     * @param string $isbn ISBN comprado.
     * @param int $quantity Cantidad solicitada.
     * @return true|'NO_STOCK'|false Resultado de compra o error funcional.
     */
    public function createDirectOrder($profileCode, $isbn, $quantity) {
        return $this->orderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>
