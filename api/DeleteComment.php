<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';
// 1. Instanciar DAO
$commentDAO = new CommentDAO();

$data = json_decode(file_get_contents("php://input"));

// Validación básica
if (
    !empty($data->isbn) && 
    !empty($data->profileCode)
) {
    // 3. Llamar a la función borrar
    if ($commentDAO->deleteComment($data->isbn, $data->profileCode)) {
        // ÉXITO: Devolvemos 200 OK
        http_response_code(200);
        echo json_encode(array("message" => "Comentario eliminado correctamente."));
    } else {
        // ERROR SQL o no encontrado
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo eliminar. Puede que no exista o no sea tuyo."));
    }
} else {
    // DATOS INCOMPLETOS
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos (Falta ISBN o ProfileCode)."));
}
?>