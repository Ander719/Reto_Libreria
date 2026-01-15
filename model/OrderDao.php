<?php
require_once '../Config/Database.php';

class OrderDao {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createDirectOrder($profileCode, $isbn, $quantity) {
        try {
            // 1. Iniciar Transacción
            $this->conn->beginTransaction();

            $stmtCheck = $this->conn->prepare("SELECT stock FROM book_ WHERE isbn = :isbn FOR UPDATE");
            $stmtCheck->bindParam(':isbn', $isbn);
            $stmtCheck->execute();
            $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);


            if (!$book || $book['stock'] < $quantity) {
                $this->conn->rollBack(); // Cancelamos transacción
                return "NO_STOCK";       // Devolvemos un código de error específico
            }
            // 2. Insertar el Pedido (ORDER_)
            // buyed = 1 (true) porque es compra directa
            $queryOrder = "INSERT INTO order_ (profile_code, date_buy, buyed) VALUES (:profile, NOW(), 1)";
            $stmtOrder = $this->conn->prepare($queryOrder);
            $stmtOrder->bindParam(':profile', $profileCode);
            $stmtOrder->execute();



            // 3. Obtener el ID del pedido recién creado
            $orderId = $this->conn->lastInsertId();

            // 4. Insertar el Contenido (CONTENT_)
            $queryContent = "INSERT INTO content_ (id_order, isbn, quantity) VALUES (:orderId, :isbn, :qty)";
            $stmtContent = $this->conn->prepare($queryContent);
            $stmtContent->bindParam(':orderId', $orderId);
            $stmtContent->bindParam(':isbn', $isbn);
            $stmtContent->bindParam(':qty', $quantity);
            $stmtContent->execute();

            // 5. Restar Stock del Libro (Opcional pero recomendable)
            $queryStock = "UPDATE book_ SET stock = stock - :qty WHERE isbn = :isbn";
            $stmtStock = $this->conn->prepare($queryStock);
            $stmtStock->bindParam(':qty', $quantity);
            $stmtStock->bindParam(':isbn', $isbn);
            $stmtStock->execute();

            // 6. Confirmar Transacción
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falla, deshacemos todo
            $this->conn->rollBack();
            // Puedes guardar el error en un log si quieres: error_log($e->getMessage());
            return false;
        }
    }
}
?>