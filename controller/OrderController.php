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
    // Sanitización: nos aseguramos de que sea un número entero
    $profileCode = filter_var($profileCode, FILTER_SANITIZE_NUMBER_INT);

    // Validación básica de seguridad
    if (empty($profileCode) || $profileCode <= 0) {
        return ["success" => false, "error" => "Código de usuario no válido."];
    }

    // El controlador pide los datos al DAO
    return $this->orderDao->getOrdersByProfile($profileCode);
}

    public function createDirectOrder($profileCode, $isbn, $quantity) {
        // Sanitización de entradas
        $profileCode = filter_var($profileCode, FILTER_SANITIZE_NUMBER_INT);
        $isbn = htmlspecialchars(trim($isbn));
        $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);

        // Validación de reglas de negocio
        if (empty($profileCode) || $profileCode <= 0) {
            return ["exito" => false, "error" => "Código de perfil no válido."];
        }
        if (empty($isbn) || strlen($isbn) < 10) {
            return ["exito" => false, "error" => "ISBN no válido."];
        }
        if ($quantity <= 0) {
            return ["exito" => false, "error" => "La cantidad debe ser mayor a cero."];
        }

        return $this->orderDao->createDirectOrder($profileCode, $isbn, $quantity);
    }
}
?>
