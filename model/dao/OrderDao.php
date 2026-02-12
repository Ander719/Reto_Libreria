<?php
require_once '../Config/Database.php';
require_once '../model/entities/Order.php';
//require_once '../model/entities/Content.php';

class OrderDao
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createDirectOrder($profileCode, $isbn, $quantity)
    {
        try {
            // 1. Iniciar Transacción
            $this->conn->beginTransaction();

            $stmtCheck = $this->conn->prepare("SELECT stock, price FROM book_ WHERE isbn = :isbn FOR UPDATE");
            $stmtCheck->bindParam(':isbn', $isbn);
            $stmtCheck->execute();
            $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);


            if (!$book || $book['stock'] < $quantity) {
                $this->conn->rollBack(); // Cancelamos transacción
                return "NO_STOCK";       // Devolvemos un código de error específico
            }

            $currentPrice = $book['price'];
            // 2. Insertar el Pedido (ORDER_)
            // buyed = 1 (true) porque es compra directa
            $queryOrder = "INSERT INTO order_ (profile_code, date_buy, buyed) VALUES (:profile, NOW(), 1)";
            $stmtOrder = $this->conn->prepare($queryOrder);
            $stmtOrder->bindParam(':profile', $profileCode);
            $stmtOrder->execute();



            // 3. Obtener el ID del pedido recién creado
            $orderId = $this->conn->lastInsertId();

            // 4. Insertar el Contenido (CONTENT_)
            $queryContent = "INSERT INTO content_ (id_order, isbn, quantity, price_moment) VALUES (:orderId, :isbn, :qty, :price)";
            $stmtContent = $this->conn->prepare($queryContent);
            $stmtContent->bindParam(':orderId', $orderId);
            $stmtContent->bindParam(':isbn', $isbn);
            $stmtContent->bindParam(':qty', $quantity);
            $stmtContent->bindParam(':price', $currentPrice);
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

    // Historial Compras
    public function getOrdersByProfile($profileCode)
    {
        $query = "SELECT o.id_order, o.date_buy, c.isbn, c.quantity, c.price_moment, b.title, b.price, b.cover 
              FROM order_ o
              JOIN content_ c ON o.id_order = c.id_order
              JOIN book_ b ON c.isbn = b.isbn
              WHERE o.profile_code = :profile
              ORDER BY o.date_buy DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':profile', $profileCode);
        $stmt->execute();
        $rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ordersMap = [];

        foreach ($rawRows as $row) {
            $orderId = $row['id_order'];

            // 1. Instanciar Order
            if (!isset($ordersMap[$orderId])) {
                $ordersMap[$orderId] = new Order(
                    $row['id_order'],
                    $profileCode,
                    $row['date_buy'],
                    1
                );
            }

            // 2. Instanciar Content (Con el precio histórico)
            $contentObj = new Content(
                $orderId,
                $row['isbn'],
                $row['quantity'],
                $row['price_moment'] // <--- USAMOS EL HISTÓRICO
            );

            // 3. Inyectar detalles visuales del libro
            $contentObj->setBookDetails(
                $row['title'],
                $row['cover']
            );

            // 4. Añadir al Order
            $ordersMap[$orderId]->addContent($contentObj);
        }

        // Convertir a JSON
        $finalList = [];
        foreach ($ordersMap as $orderObj) {
            $finalList[] = $orderObj->toArray();
        }

        return $finalList;
    }
}
