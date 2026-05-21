<?php
require_once '../model/entities/Comment.php';

/**
 * Consultas y escrituras de resenas.
 */
class CommentDAO {
    private $conn;

    /**
     * @param PDO $db Conexion PDO reutilizada por el DAO.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Inserta una resena saneando el texto antes de guardarlo.
     *
     * @param Comment $comment Comentario validado en la API.
     * @return bool True si se inserta correctamente.
     */
    public function createComment(Comment $comment) {
        $query = "INSERT INTO comment_ (profile_code, Isbn, comment_text, valoration, date_comment) 
                  VALUES (:profile, :isbn, :text, :rating, :date)";
        
        $stmt = $this->conn->prepare($query);

        $cleanText = htmlspecialchars(strip_tags($comment->getCommentText()));

        $stmt->bindValue(':profile', $comment->getProfileCode());
        $stmt->bindValue(':isbn',    $comment->getIsbn());
        $stmt->bindValue(':text',    $cleanText);
        $stmt->bindValue(':rating',  $comment->getRating());
        $stmt->bindValue(':date',    $comment->getDateComment());

        return $stmt->execute();
    }

    /**
     * Trae las resenas de un libro con el nombre visible del usuario.
     *
     * @param string $isbn ISBN del libro.
     * @return Comment[] Lista de entidades Comment.
     */
    public function getCommentsByISBN($isbn) {
        // Solo necesitamos user_name de profile_; el resto del perfil no se expone.
        $query = "SELECT c.profile_code,
                         c.Isbn,
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
        
        $resultList = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $commentObj = new Comment();
            $commentObj->setProfileCode($row['profile_code']);
            $commentObj->setIsbn($row['Isbn']);
            $commentObj->setCommentText($row['comment_text']);
            $commentObj->setRating($row['valoration']);
            $commentObj->setDateComment($row['date_comment']);

            $commentObj->setUserName($row['user_name']);
            $resultList[] = $commentObj;
        }
        
        return $resultList;
    }

    /**
     * Actualiza una resena usando ISBN y perfil como clave.
     *
     * @param Comment $comment Entidad con claves y datos nuevos.
     * @return bool True si la sentencia se ejecuta correctamente.
     */
    public function updateComment(Comment $comment) {
        $query = "UPDATE comment_ 
                  SET comment_text = :text, 
                      valoration = :rating 
                  WHERE Isbn = :isbn AND profile_code = :profileCode";
                  
        $stmt = $this->conn->prepare($query);
        
        // El texto se sanea tambien en actualizacion para evitar HTML persistente.
        $cleanText = htmlspecialchars(strip_tags($comment->getCommentText()));

        $stmt->bindValue(':text',        $cleanText);
        $stmt->bindValue(':rating',      $comment->getRating());
        $stmt->bindValue(':isbn',        $comment->getIsbn());
        $stmt->bindValue(':profileCode', $comment->getProfileCode());
        
        return $stmt->execute();
    }

    /**
     * Borra una resena y comprueba que existia.
     *
     * @param string $isbn ISBN comentado.
     * @param int $profileCode Perfil propietario del comentario.
     * @return bool True solo si se elimina al menos una fila.
     */
    public function deleteComment($isbn, $profileCode) {
        $query = "DELETE FROM comment_ WHERE Isbn = :isbn AND profile_code = :profileCode";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':profileCode', $profileCode);
        
        if($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        return false;
    }
}
?>
