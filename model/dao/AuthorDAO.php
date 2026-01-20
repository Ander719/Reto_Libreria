<?php
// Usamos dirname para ir a la raíz del proyecto de forma segura
require_once dirname(__DIR__, 2) . '/Config/Database.php'; 
require_once dirname(__DIR__) . '/entities/Author.php';

class AuthorDAO {
    private $conn;
    private $table_name = "author_";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getOrCreateAuthorId($name, $surname) {
        // 1. Verificar si existe
        $query = "SELECT ID_AUTHOR FROM " . $this->table_name . " WHERE name_author = :name AND last_name = :surname LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['ID_AUTHOR'];
        }

        // 2. Si no existe, crear nuevo
        // Obtenemos el ID máximo actual (si no usas AUTO_INCREMENT)
        $queryMax = "SELECT MAX(ID_AUTHOR) as max_id FROM " . $this->table_name;
        $stmtMax = $this->conn->prepare($queryMax);
        $stmtMax->execute();
        $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $newId = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

        $queryInsert = "INSERT INTO " . $this->table_name . " (ID_AUTHOR, name_author, last_name) VALUES (:id, :name, :surname)";
        $stmtInsert = $this->conn->prepare($queryInsert);
        $stmtInsert->bindParam(":id", $newId);
        $stmtInsert->bindParam(":name", $name);
        $stmtInsert->bindParam(":surname", $surname);

        if ($stmtInsert->execute()) {
            return $newId;
        }
        return false;
    }
}
?>