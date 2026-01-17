<?php
require_once __DIR__ . '/../model/dao/CommentDAO.php';

class CommentController {

    private $commentDAO;

    public function __construct() {
        $this->commentDAO = new CommentDAO();
    }
    public function getCommentsByISBN($isbn) {
        return $this->commentDAO->getCommentsByISBN($isbn);
    }
}
?>