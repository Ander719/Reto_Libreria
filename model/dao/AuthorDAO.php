<?php
// model/dao/AuthorDAO.php — Capa de acceso a datos para autores (usa PDO con sentencias preparadas)
require_once dirname(__DIR__, 2) . '/Config/Database.php'; 
require_once dirname(__DIR__) . '/entities/Author.php';

class AuthorDAO {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene el ID de un autor existente o crea uno nuevo si no existe.
     * Usa sentencias preparadas con bindParam para prevenir inyección SQL.
     */
    public function getOrCreateAuthorId($name, $surname) {
        try {
            // Verificamos si el autor ya existe en la base de datos
            $query = "SELECT ID_AUTHOR FROM author_ WHERE name_author = :name AND last_name = :surname LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":surname", $surname);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['ID_AUTHOR'];
            }

            // Si no existe, creamos un nuevo autor con el siguiente ID disponible
            $queryMax = "SELECT MAX(ID_AUTHOR) as max_id FROM author_";
            $stmtMax = $this->conn->prepare($queryMax);
            $stmtMax->execute();
            $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $newId = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

            $queryInsert = "INSERT INTO author_ (ID_AUTHOR, name_author, last_name) VALUES (:id, :name, :surname)";
            $stmtInsert = $this->conn->prepare($queryInsert);
            $stmtInsert->bindParam(":id", $newId);
            $stmtInsert->bindParam(":name", $name);
            $stmtInsert->bindParam(":surname", $surname);

            if ($stmtInsert->execute()) {
                return $newId;
            }
            return false;

        } catch (PDOException $e) {
            error_log("Error en AuthorDAO::getOrCreateAuthorId: " . $e->getMessage());
            return false;
        }
    }
}
?>