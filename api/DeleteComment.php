<?php
// api/DeleteComment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../Config/Database.php';
require_once '../model/dao/CommentDAO.php';
require_once '../Config/Session.php'; 

// Desactivamos errores visibles para asegurar que la respuesta sea siempre JSON válido
error_reporting(0);
ini_set('display_errors', 0);

$commentDAO = new CommentDAO();
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->isbn) && !empty($data->profileCode)) {

    // 1. Verificar sesión iniciada
    session_start();
    if (!isset($_SESSION['user'])) {
        http_response_code(401); 
        echo json_encode(["message" => "No has iniciado sesión."]); 
        exit();
    }
    
    // Obtener ID del usuario logueado de forma segura (objeto o array)
    $user = $_SESSION['user'];
    $loggedId = is_object($user) ? ($user->profile_code ?? $user->profileCode) : $user['profile_code'];

    // 2. Verificar si el usuario logueado es ADMIN (Versión PDO)
    $db = new Database();
    $conn = $db->getConnection();
    $isAdmin = false;

    if ($conn) {
        // Usamos parámetros nombrados (:id) típico de PDO
        $sql = "SELECT profile_code FROM admin_ WHERE profile_code = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $loggedId]);
        
        // Si fetch devuelve una fila, es que es admin
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $isAdmin = true;
        }
    }

    // 3. Verificar PERMISOS
    // Permitir si el usuario logueado es el dueño del comentario O si es Admin
    if ($loggedId == $data->profileCode || $isAdmin) {
        
        // 4. BORRAR
        // ¡CORRECCIÓN CRÍTICA AQUÍ!: Tu DAO espera ($isbn, $profileCode)
        // Antes se enviaba al revés y por eso fallaba.
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