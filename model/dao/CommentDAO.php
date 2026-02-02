<?php
require_once '../Config/Database.php';
require_once '../model/entities/Comment.php';

class CommentDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createComment(Comment $comment) {
        $query = "INSERT INTO comment_ (profile_code, Isbn, comment_text, valoration, date_comment) 
                  VALUES (:profile, :isbn, :text, :rating, :date)";
        
        $stmt = $this->conn->prepare($query);

        // Limpieza de etiquetas HTML para seguridad
        $cleanText = htmlspecialchars(strip_tags($comment->getCommentText()));

        $stmt->bindValue(':profile', $comment->getProfileCode());
        $stmt->bindValue(':isbn',    $comment->getIsbn());
        $stmt->bindValue(':text',    $cleanText);
        $stmt->bindValue(':rating',  $comment->getRating());
        $stmt->bindValue(':date',    $comment->getDateComment());

        return $stmt->execute();
    }

    public function getCommentsByISBN($isbn) {
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
            // Creamos el objeto correctamente
            $commentObj = new Comment();
            
            $commentObj->setProfileCode($row['profile_code']);
            $commentObj->setCommentText($row['comment_text']);
            $commentObj->setRating($row['valoration']);
            $commentObj->setDateComment($row['date_comment']);

            // Mapeamos la salida para mantener compatibilidad con tu JS actual
            $resultArray[] = [
                'profile_code' => $commentObj->getProfileCode(), // JS usa esto
                'comment_text' => $commentObj->getCommentText(), // JS usa esto
                'valoration'   => $commentObj->getRating(),      // JS usa esto
                'dateComent'   => $commentObj->getDateComment(), // JS usa esto
                'user_name'    => $row['user_name']
            ];
        }
        
        return $resultArray;
    }

    // Update recibiendo un Objeto para ser consistente
    public function updateComment(Comment $comment) {
        $query = "UPDATE comment_ 
                  SET comment_text = :text, 
                      valoration = :rating 
                  WHERE Isbn = :isbn AND profile_code = :profileCode";
                  
        $stmt = $this->conn->prepare($query);
        
        $cleanText = htmlspecialchars(strip_tags($comment->getCommentText()));

        $stmt->bindValue(':text',        $cleanText);
        $stmt->bindValue(':rating',      $comment->getRating());
        $stmt->bindValue(':isbn',        $comment->getIsbn());
        $stmt->bindValue(':profileCode', $comment->getProfileCode());
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

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