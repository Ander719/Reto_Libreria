<?php
require_once '../Config/db.php'; // Asumo que aquí tienes tu conexión a BDD

class CommentModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Función para crear un comentario (Create del CRUD)
    public function createComment($profileCode, $isbn, $comment, $valoration, $date) {
        // Query preparada para evitar Inyección SQL (Seguridad IL8.4)
        $query = "INSERT INTO COMENT_ (PROFILE_CODE, Isbn, comment_text, valoration, dateComent) 
                  VALUES (:profile, :isbn, :comment, :rating, :date)";

        $stmt = $this->conn->prepare($query);

        // Sanear datos (limpiar etiquetas HTML maliciosas)
        $comment = htmlspecialchars(strip_tags($comment));

        // Vincular parámetros
        $stmt->bindParam(':profile', $profileCode);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':rating', $valoration);
        $stmt->bindParam(':date', $date);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>