<?php
// api/DeleteComment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../Config/Database.php';
require_once '../model/dao/CommentDAO.php';

// Desactivamos errores visibles para asegurar JSON limpio
error_reporting(0);
ini_set('display_errors', 0);

$commentDAO = new CommentDAO();
$data = json_decode(file_get_contents("php://input"));

// Verificamos que lleguen los datos
if (!empty($data->isbn) && !empty($data->profileCode)) {

    // 1. Gestión de Sesión
    // Si tienes un archivo Session.php que hace session_start, úsalo.
    // Si no, usa session_start() con @ para evitar warnings si ya está iniciada.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user'])) {
        http_response_code(401); 
        echo json_encode(["message" => "No has iniciado sesión."]); 
        exit();
    }
    
    // Obtener ID del usuario logueado
    $user = $_SESSION['user'];
    // Compatible con si guardas el usuario como Array o como Objeto
    $loggedId = is_object($user) ? ($user->profile_code ?? $user->profileCode) : ($user['profile_code'] ?? $user['profileCode']);

    // 2. Verificar si es ADMIN
    // (Lo ideal sería tener un UserDAO->isAdmin($id), pero mantenemos tu lógica SQL aquí para no obligarte a crear más archivos)
    $db = new Database();
    $conn = $db->getConnection();
    $isAdmin = false;

    if ($conn) {
        $sql = "SELECT profile_code FROM admin_ WHERE profile_code = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $loggedId]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $isAdmin = true;
        }
    }

    // 3. Verificar PERMISOS: Solo dueño o Admin
    // Comparamos el ID de la sesión con el ID del dueño del comentario que viene del JS
    if ($loggedId == $data->profileCode || $isAdmin) {
        
        // Llamamos al DAO (que ya está corregido en los pasos anteriores)
        if ($commentDAO->deleteComment($data->isbn, $data->profileCode)) {
            http_response_code(200);
            echo json_encode(["message" => "Comentario eliminado correctamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Error al intentar borrar en la base de datos."]);
        }

    } else {
        http_response_code(403);
        echo json_encode(["message" => "No tienes permiso para borrar este comentario."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["message" => "Faltan datos (ISBN o ProfileCode)."]);
}
?>