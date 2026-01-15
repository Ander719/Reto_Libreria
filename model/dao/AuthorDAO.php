<?php
require_once '../../Config/Database.php';

class AuthorDAO {
    private $conn;
    private $table_name = "AUTHOR_";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getOrCreateAuthorId($name, $surname) {
        // 1. Buscar si ya existe
        $query = "SELECT ID_AUTHOR FROM " . $this->table_name . " WHERE NameAuthor = :name AND LastName = :surname LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['ID_AUTHOR'];
        }

        // 2. Si no existe, calcular MAX(ID) + 1
        $queryMax = "SELECT MAX(ID_AUTHOR) as max_id FROM " . $this->table_name;
        $stmtMax = $this->conn->prepare($queryMax);
        $stmtMax->execute();
        $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
        
        $newId = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

        // 3. Insertar nuevo autor
        $queryInsert = "INSERT INTO " . $this->table_name . " (ID_AUTHOR, NameAuthor, LastName) VALUES (:id, :name, :surname)";
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