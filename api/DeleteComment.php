<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$controller = new CommentController();

if (!empty($data->isbn) && !empty($data->profileCode)) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user'])) {
        $response = $controller->deleteComment($data->isbn, $data->profileCode, $_SESSION['user']);
        
        http_response_code($response["code"]);
        echo json_encode([
            "success" => $response["success"],
            "message" => $response["message"]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Debes iniciar sesión."
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos."
    ]);
}
?>