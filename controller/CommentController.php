<?php
class CommentController {

    private $commentDAO;

    public function __construct() {
        $this->commentDAO = new CommentDAO();
    }
}
?>