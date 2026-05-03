<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/dao/CommentDAO.php';
require_once __DIR__ . '/../model/entities/Comment.php';

class CommentController {

    private $commentDAO;
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->commentDAO = new CommentDAO($db);
    }

    // LEER
    public function getCommentsByISBN($isbn) {
        return $this->commentDAO->getCommentsByISBN($isbn);
    }

    // CREAR
    public function addComment(Comment $comment) {
        return $this->commentDAO->createComment($comment);
    }

    // ACTUALIZAR 
    public function updateComment(Comment $comment) {
        return $this->commentDAO->updateComment($comment);
    }

    // BORRAR
    public function deleteComment($isbn, $targetProfileCode) {
        return $this->commentDAO->deleteComment($isbn, $targetProfileCode);
    }
}
?>
