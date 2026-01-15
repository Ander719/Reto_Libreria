<?php
require_once '../../Config/Database.php';

class CommentDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createComment($profileCode, $isbn, $comment, $valoration, $date) {
        $query = "INSERT INTO COMENT_ (PROFILE_CODE, Isbn, coment, valoration, dateComent) 
                  VALUES (:profile, :isbn, :comment, :rating, :date)";
        $stmt = $this->conn->prepare($query);
        $comment = htmlspecialchars(strip_tags($comment));
        $stmt->bindParam(':profile', $profileCode);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':rating', $valoration);
        $stmt->bindParam(':date', $date);
        return $stmt->execute();
    }

    public function getCommentsByISBN($isbn) {
        $query = "SELECT c.PROFILE_CODE, c.coment as comment_text, c.valoration, c.dateComent, p.USER_NAME 
                  FROM COMENT_ c
                  JOIN PROFILE_ p ON c.PROFILE_CODE = p.PROFILE_CODE
                  WHERE c.Isbn = :isbn
                  ORDER BY c.dateComent DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteComment($isbn, $profileCode) {
        $query = "DELETE FROM COMENT_ WHERE Isbn = :isbn AND PROFILE_CODE = :profileCode";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':profileCode', $profileCode);
        if($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function updateComment($isbn, $profileCode, $text, $rating) {
        $query = "UPDATE COMENT_ SET coment = :text, valoration = :rating 
                  WHERE Isbn = :isbn AND PROFILE_CODE = :profileCode";
        $stmt = $this->conn->prepare($query);
        $text = htmlspecialchars(strip_tags($text));
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':profileCode', $profileCode);
        
        if($stmt->execute()) {
            return $stmt->rowCount();
        }
        return false;
    }
}
?>