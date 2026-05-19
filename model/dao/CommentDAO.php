<?php
// model/dao/CommentDAO.php — Capa de acceso a datos para comentarios (usa PDO con sentencias preparadas)
require_once dirname(__DIR__, 2) . '/Config/Database.php';
require_once dirname(__DIR__) . '/entities/Comment.php';

class CommentDAO {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo comentario en la base de datos.
     * Usa sentencias preparadas con bindValue para prevenir inyección SQL.
     * Se sanitiza el texto del comentario antes de insertarlo.
     */
    public function createComment(Comment $comment) {
        try {
            $query = "INSERT INTO comment_ (profile_code, Isbn, comment_text, valoration, date_comment) 
                      VALUES (:profile, :isbn, :text, :rating, :date)";
            
            $stmt = $this->conn->prepare($query);

            // Los datos se almacenan sin sanitizar; la sanitización se hace en el frontend al mostrar
            $cleanText = $comment->getCommentText();

            $stmt->bindValue(':profile', $comment->getProfileCode());
            $stmt->bindValue(':isbn',    $comment->getIsbn());
            $stmt->bindValue(':text',    $cleanText);
            $stmt->bindValue(':rating',  $comment->getRating());
            $stmt->bindValue(':date',    $comment->getDateComment());

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en CommentDAO::createComment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los comentarios asociados a un ISBN.
     * Usa sentencia preparada con bindParam.
     */
    public function getCommentsByISBN($isbn) {
        try {
            $query = "SELECT c.profile_code, 
                             c.comment_text, 
                             c.valoration, 
                             c.date_comment, 
                             p.user_name 
                      FROM comment_ c
                      JOIN profile_ p ON c.profile_code = p.profile_code
                      WHERE c.Isbn = :isbn
                      ORDER BY c.date_comment DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->execute();
            
            $resultArray = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Instanciamos la Entidad Comment con los datos de la BD
                $commentObj = new Comment();
                $commentObj->setProfileCode($row['profile_code']);
                $commentObj->setCommentText($row['comment_text']);
                $commentObj->setRating($row['valoration']);
                $commentObj->setDateComment($row['date_comment']);

                // Usamos toArray() para obtener los datos limpios
                $data = $commentObj->toArray();
                
                // Añadimos el dato extra del JOIN (user_name) que no está en la entidad Comment
                $data['user_name'] = $row['user_name'];

                $resultArray[] = $data;
            }
            
            return $resultArray;
        } catch (PDOException $e) {
            error_log("Error en CommentDAO::getCommentsByISBN: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza un comentario existente.
     * Usa sentencia preparada con bindValue.
     */
    public function updateComment(Comment $comment) {
        try {
            $query = "UPDATE comment_ 
                      SET comment_text = :text, 
                          valoration = :rating 
                      WHERE Isbn = :isbn AND profile_code = :profileCode";
                      
            $stmt = $this->conn->prepare($query);
            
            // Los datos se almacenan sin sanitizar; la sanitización se hace en el frontend al mostrar
            $cleanText = $comment->getCommentText();

            $stmt->bindValue(':text',        $cleanText);
            $stmt->bindValue(':rating',      $comment->getRating());
            $stmt->bindValue(':isbn',        $comment->getIsbn());
            $stmt->bindValue(':profileCode', $comment->getProfileCode());
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en CommentDAO::updateComment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un comentario por ISBN y código de perfil.
     * Usa sentencia preparada con bindParam.
     */
    public function deleteComment($isbn, $profileCode) {
        try {
            $query = "DELETE FROM comment_ WHERE Isbn = :isbn AND profile_code = :profileCode";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':profileCode', $profileCode);
            
            if($stmt->execute()) {
                return $stmt->rowCount() > 0;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en CommentDAO::deleteComment: " . $e->getMessage());
            return false;
        }
    }
}
?>