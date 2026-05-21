<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/dao/CommentDAO.php';
require_once __DIR__ . '/../model/entities/Comment.php';

/**
 * Puente entre la API de resenas y CommentDAO.
 */
class CommentController {

    private $commentDAO;
    /**
     * Prepara el DAO de comentarios.
     */
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->commentDAO = new CommentDAO($db);
    }

    /**
     * Devuelve las resenas de un libro.
     *
     * @param string $isbn ISBN del libro.
     * @return Comment[] Comentarios encontrados.
     */
    public function getCommentsByISBN($isbn) {
        return $this->commentDAO->getCommentsByISBN($isbn);
    }

    /**
     * Inserta una nueva resena.
     *
     * @param Comment $comment Entidad validada desde la API.
     * @return bool True si se crea correctamente.
     */
    public function addComment(Comment $comment) {
        return $this->commentDAO->createComment($comment);
    }

    /**
     * Guarda cambios en una resena existente.
     *
     * @param Comment $comment Entidad con texto, valoracion y claves.
     * @return bool True si la actualizacion afecta a una fila.
     */
    public function updateComment(Comment $comment) {
        return $this->commentDAO->updateComment($comment);
    }

    /**
     * Borra una resena concreta de un libro.
     *
     * @param string $isbn ISBN del libro.
     * @param int $targetProfileCode Perfil propietario de la resena.
     * @return bool True si se elimina una fila.
     */
    public function deleteComment($isbn, $targetProfileCode) {
        return $this->commentDAO->deleteComment($isbn, $targetProfileCode);
    }
}
?>
