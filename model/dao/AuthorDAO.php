<?php
// Usamos dirname para ir a la raíz del proyecto de forma segura
require_once dirname(__DIR__, 2) . '/Config/Database.php'; 
require_once dirname(__DIR__) . '/entities/Author.php';

/**
 * DAO responsable de reutilizar o crear autores del catalogo.
 */
class AuthorDAO {
    private $conn;

    /**
     * Permite usar una conexion inyectada o crear una propia si se invoca de forma aislada.
     *
     * @param PDO|null $db Conexion PDO opcional.
     */
    public function __construct($db = null) {
        if ($db !== null) {
            $this->conn = $db;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Busca un autor por nombre y apellido; si no existe, lo inserta y devuelve su ID.
     *
     * @param string $name Nombre del autor.
     * @param string $surname Apellido del autor.
     * @return int|false ID del autor o false si falla la insercion.
     */
    public function getOrCreateAuthorId($name, $surname) {
        $query = "SELECT ID_AUTHOR FROM author_ WHERE name_author = :name AND last_name = :surname LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['ID_AUTHOR'];
        }

        // La tabla no usa AUTO_INCREMENT; se calcula el siguiente ID antes de insertar.
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
    }
}
?>
