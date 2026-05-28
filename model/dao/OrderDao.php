<?php
require_once '../model/entities/Order.php';
//require_once '../model/entities/Content.php';

// Consultas y escrituras de pedidos.
class OrderDao
{
    private $conn;

    // Guarda la conexion PDO.
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crea un pedido, descuenta stock y lo marca como comprado.
    public function createDirectOrder($profileCode, $isbn, $quantity)
    {
        try {
            // Pedido, linea y stock van juntos o no se guarda nada.
            $this->conn->beginTransaction();

            // FOR UPDATE bloquea el libro mientras se revisa el stock.
            $stmtCheck = $this->conn->prepare("SELECT stock, price FROM book_ WHERE isbn = :isbn FOR UPDATE");
            $stmtCheck->bindParam(':isbn', $isbn);
            $stmtCheck->execute();
            $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);


            if (!$book || $book['stock'] < $quantity) {
                $this->conn->rollBack();
                return "NO_STOCK";
            }

            $currentPrice = $book['price'];
            // buyed = 1 porque este flujo confirma la compra en el mismo paso.
            $queryOrder = "INSERT INTO order_ (profile_code, date_buy, buyed) VALUES (:profile, NOW(), 1)";
            $stmtOrder = $this->conn->prepare($queryOrder);
            $stmtOrder->bindParam(':profile', $profileCode);
            $stmtOrder->execute();



            $orderId = $this->conn->lastInsertId();

            $queryContent = "INSERT INTO content_ (id_order, isbn, quantity, price_moment) VALUES (:orderId, :isbn, :qty, :price)";
            $stmtContent = $this->conn->prepare($queryContent);
            $stmtContent->bindParam(':orderId', $orderId);
            $stmtContent->bindParam(':isbn', $isbn);
            $stmtContent->bindParam(':qty', $quantity);
            $stmtContent->bindParam(':price', $currentPrice);
            $stmtContent->execute();

            $queryStock = "UPDATE book_ SET stock = stock - :qty WHERE isbn = :isbn";
            $stmtStock = $this->conn->prepare($queryStock);
            $stmtStock->bindParam(':qty', $quantity);
            $stmtStock->bindParam(':isbn', $isbn);
            $stmtStock->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo rompe, se deshace tambien el stock.
            $this->conn->rollBack();
            return false;
        }
    }

    // Obtiene el historial de pedidos de un perfil.
    public function getOrdersByProfile($profileCode)
    {
        // La consulta ya trae portada y titulo para no hacer otro fetch por cada item.
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

            if (!isset($ordersMap[$orderId])) {
                $ordersMap[$orderId] = new Order(
                    $row['id_order'],
                    $profileCode,
                    $row['date_buy'],
                    1
                );
            }

            $contentObj = new Content(
                $orderId,
                $row['isbn'],
                $row['quantity'],
                $row['price_moment']
            );

            $contentObj->setBookDetails(
                $row['title'],
                $row['cover']
            );

            $ordersMap[$orderId]->addContent($contentObj);
        }

        $finalList = [];
        foreach ($ordersMap as $orderObj) {
            $finalList[] = $orderObj->toArray();
        }

        return $finalList;
    }
}
