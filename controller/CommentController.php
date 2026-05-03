<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/dao/CommentDAO.php';
require_once __DIR__ . '/../model/dao/ProfileDAO.php';
require_once __DIR__ . '/../model/entities/Comment.php';

class CommentController {

    private $commentDAO;
    private $profileDAO;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->commentDAO = new CommentDAO($db);
        $this->profileDAO = new ProfileDAO($db);
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
    public function deleteComment($isbn, $targetProfileCode, $currentUser) {
        $loggedId = is_object($currentUser) ? ($currentUser->profile_code ?? $currentUser->profileCode) : ($currentUser['profile_code'] ?? $currentUser['profileCode']);

        if (!$loggedId) {
            return ["success" => false, "message" => "Usuario no identificado.", "code" => 401];
        }

        // Verificar si es ADMIN
        $isAdmin = $this->checkIfAdmin($loggedId);

        // Permisos: Solo el dueño o el admin pueden borrar
        if ($loggedId == $targetProfileCode || $isAdmin) {
            if ($this->commentDAO->deleteComment($isbn, $targetProfileCode)) {
                return ["success" => true, "message" => "Comentario eliminado.", "code" => 200];
            } else {
                return ["success" => false, "message" => "Error en BBDD al eliminar.", "code" => 503];
            }
        } else {
            return ["success" => false, "message" => "No tienes permisos para borrar este comentario.", "code" => 403];
        }
    }

    private function checkIfAdmin($profileCode) {
        return $this->profileDAO->isAdminByProfileCode($profileCode);
    }
}
?>
