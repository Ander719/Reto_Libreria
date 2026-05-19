<?php
// CommentController.php — Controlador para la gestión de comentarios y reseñas
// Actúa como un puente ciego entre la API y la capa de acceso a datos (DAO)
require_once __DIR__ . '/../model/dao/CommentDAO.php';

class CommentController {

    private $commentDAO;

    public function __construct($db) {
        $this->commentDAO = new CommentDAO($db);
    }

    // Solicita al DAO los comentarios asociados a un libro mediante su ISBN
    public function getCommentsByISBN($isbn) {
        return $this->commentDAO->getCommentsByISBN($isbn);
    }

    // Delegamos la creación de un nuevo comentario al DAO
    public function addComment($comment) {
        return $this->commentDAO->createComment($comment);
    }

    // Delegamos la actualización de un comentario existente al DAO
    public function updateComment($comment) {
        return $this->commentDAO->updateComment($comment);
    }

    // Solicita al DAO la eliminación de un comentario específico
    public function deleteComment($isbn, $profileCode) {
        return $this->commentDAO->deleteComment($isbn, $profileCode);
    }
}
?>