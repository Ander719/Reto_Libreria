<?php
require_once __DIR__ . '/../model/dao/CommentDAO.php';
require_once __DIR__ . '/../model/entities/Comment.php';
require_once __DIR__ . '/../Config/Database.php';

class CommentController {

    private $commentDAO;

    public function __construct() {
        $this->commentDAO = new CommentDAO();
    }

    // --- LEER (GET) ---
    public function getCommentsByISBN($isbn) {
        $cleanIsbn = trim(htmlspecialchars($isbn));
        return $this->commentDAO->getCommentsByISBN($cleanIsbn);
    }

    // --- CREAR (POST) ---
    public function addComment($data) {
        // Validación de existencia de datos
        if (empty($data->profileCode) || empty($data->isbn) || empty($data->text) || empty($data->rating)) {
            return ["success" => false, "message" => "Datos incompletos.", "code" => 400];
        }

        // 1. SANITIZACIÓN Y VALIDACIÓN
        $cleanProfile = trim(htmlspecialchars($data->profileCode));
        $cleanIsbn    = trim(htmlspecialchars($data->isbn));
        $cleanText    = trim(htmlspecialchars($data->text)); // IMPORTANTE: Evita XSS
        
        // Validar que rating es un entero
        $cleanRating  = filter_var($data->rating, FILTER_VALIDATE_FLOAT);

        // Validar rango lógico (1 a 5 estrellas)
        if ($cleanRating === false || $cleanRating < 0 || $cleanRating > 5) {
            return ["success" => false, "message" => "La puntuación debe ser entre 0 y 5.", "code" => 400];
        }

        $newComment = new Comment();
        $newComment->setProfileCode($cleanProfile);
        $newComment->setIsbn($cleanIsbn);
        $newComment->setCommentText($cleanText); 
        $newComment->setRating($cleanRating);
        $newComment->setDateComment($data->date ?? date('Y-m-d'));

        if ($this->commentDAO->createComment($newComment)) {
            return ["success" => true, "message" => "Comentario publicado correctamente.", "code" => 201];
        } else {
            return ["success" => false, "message" => "Error al guardar el comentario.", "code" => 503];
        }
    }

    // --- ACTUALIZAR (UPDATE) ---
    public function updateComment($data) {
        if (empty($data->isbn) || empty($data->profileCode) || empty($data->text) || empty($data->rating)) {
            return ["success" => false, "message" => "Faltan datos para actualizar.", "code" => 400];
        }

        // 1. SANITIZACIÓN Y VALIDACIÓN
        $cleanProfile = trim(htmlspecialchars($data->profileCode));
        $cleanIsbn    = trim(htmlspecialchars($data->isbn));
        $cleanText    = trim(htmlspecialchars($data->text));
        $cleanRating  = filter_var($data->rating, FILTER_VALIDATE_FLOAT);

        if ($cleanRating === false || $cleanRating < 0 || $cleanRating > 5) {
             return ["success" => false, "message" => "Valoración inválida.", "code" => 400];
        }

        $commentToUpdate = new Comment();
        $commentToUpdate->setIsbn($cleanIsbn);
        $commentToUpdate->setProfileCode($cleanProfile);
        $commentToUpdate->setCommentText($cleanText);
        $commentToUpdate->setRating($cleanRating);

        if ($this->commentDAO->updateComment($commentToUpdate)) {
            return ["success" => true, "message" => "Comentario actualizado.", "code" => 200];
        } else {
            return ["success" => false, "message" => "No se pudo actualizar o no hubo cambios.", "code" => 503];
        }
    }

    // --- BORRAR (DELETE) ---
    public function deleteComment($isbn, $targetProfileCode, $currentUser) {
        // Sanitización
        $cleanIsbn = trim(htmlspecialchars($isbn));
        $cleanTargetProfile = trim(htmlspecialchars($targetProfileCode));

        // $currentUser viene de la sesión. Verificamos quién es.
        $loggedId = is_object($currentUser) ? ($currentUser->profile_code ?? $currentUser->profileCode) : ($currentUser['profile_code'] ?? $currentUser['profileCode']);

        if (!$loggedId) {
            return ["success" => false, "message" => "Usuario no identificado.", "code" => 401];
        }

        // Verificar si es ADMIN
        $isAdmin = $this->checkIfAdmin($loggedId);

        // Permisos: Solo el dueño o el admin pueden borrar
        if ($loggedId == $cleanTargetProfile || $isAdmin) {
            if ($this->commentDAO->deleteComment($cleanIsbn, $cleanTargetProfile)) {
                return ["success" => true, "message" => "Comentario eliminado.", "code" => 200];
            } else {
                return ["success" => false, "message" => "Error en BBDD al eliminar.", "code" => 503];
            }
        } else {
            return ["success" => false, "message" => "No tienes permisos para borrar este comentario.", "code" => 403];
        }
    }

    // Método Privado Auxiliar
    private function checkIfAdmin($profileCode) {
        $db = new Database();
        $conn = $db->getConnection();
        if ($conn) {
            $sql = "SELECT profile_code FROM admin_ WHERE profile_code = :id";
            $stmt = $conn->prepare($sql);
            // profileCode ya debería venir limpio, pero la consulta preparada protege de inyección SQL
            $stmt->execute([':id' => $profileCode]);
            return ($stmt->fetch(PDO::FETCH_ASSOC)) ? true : false;
        }
        return false;
    }
}
?>