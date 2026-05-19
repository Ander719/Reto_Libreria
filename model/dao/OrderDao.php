<?php
// model/dao/OrderDao.php — Capa de acceso a datos para pedidos (usa PDO con sentencias preparadas)
require_once dirname(__DIR__, 2) . '/Config/Database.php';
require_once dirname(__DIR__) . '/entities/Order.php';

class OrderDao
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Crea una orden de compra directa con transacción atómica.
     * Verifica stock, inserta orden, contenido y actualiza stock.
     * Usa sentencias preparadas con bindParam en todas las consultas.
     */
    public function createDirectOrder($profileCode, $isbn, $quantity)
    {
        try {
            // 1. Iniciar Transacción para garantizar atomicidad
            $this->conn->beginTransaction();

            // Verificar stock disponible con bloqueo FOR UPDATE
            $stmtCheck = $this->conn->prepare("SELECT stock, price FROM book_ WHERE isbn = :isbn FOR UPDATE");
            $stmtCheck->bindParam(':isbn', $isbn);
            $stmtCheck->execute();
            $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$book || $book['stock'] < $quantity) {
                $this->conn->rollBack();
                return "NO_STOCK";
            }

            $currentPrice = $book['price'];

            // 2. Insertar el Pedido (ORDER_)
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

            // 5. Restar Stock del Libro
            $queryStock = "UPDATE book_ SET stock = stock - :qty WHERE isbn = :isbn";
            $stmtStock = $this->conn->prepare($queryStock);
            $stmtStock->bindParam(':qty', $quantity);
            $stmtStock->bindParam(':isbn', $isbn);
            $stmtStock->execute();

            // 6. Confirmar Transacción — todas las operaciones se aplican o ninguna
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo falla, deshacemos todo
            $this->conn->rollBack();
            error_log("Error en OrderDao::createDirectOrder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el historial de pedidos de un usuario por su profile_code.
     * Usa sentencia preparada con bindParam.
     */
    public function getOrdersByProfile($profileCode)
    {
        try {
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

                // 1. Instanciar Order (si no existe en el mapa)
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
                    $row['price_moment']
                );

                // 3. Inyectar detalles visuales del libro
                $contentObj->setBookDetails(
                    $row['title'],
                    $row['cover']
                );

                // 4. Añadir al Order
                $ordersMap[$orderId]->addContent($contentObj);
            }

            // Convertir a arrays para serialización JSON
            $finalList = [];
            foreach ($ordersMap as $orderObj) {
                $finalList[] = $orderObj->toArray();
            }

            return $finalList;
        } catch (PDOException $e) {
            error_log("Error en OrderDao::getOrdersByProfile: " . $e->getMessage());
            return [];
        }
    }
}
