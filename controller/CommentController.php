<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/dao/CommentDAO.php';
require_once __DIR__ . '/../model/entities/Comment.php';

// Puente entre la API de resenas y CommentDAO
class CommentController {

    private $commentDAO;
    // Prepara el DAO de comentarios
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->commentDAO = new CommentDAO($db);
    }

    // Devuelve las resenas de un libro
    public function getCommentsByISBN($isbn) {
        return $this->commentDAO->getCommentsByISBN($isbn);
    }

    // Inserta una resena nueva
    public function addComment(Comment $comment) {
        return $this->commentDAO->createComment($comment);
    }

    // Guarda cambios en una resena que ya existe
    public function updateComment(Comment $comment) {
        return $this->commentDAO->updateComment($comment);
    }

    // Borra una resena de un libro
    public function deleteComment($isbn, $targetProfileCode) {
        return $this->commentDAO->deleteComment($isbn, $targetProfileCode);
    }
}
?>
