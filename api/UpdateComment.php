<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../model/dao/CommentDAO.php';
include_once '../model/entities/Comment.php';

$commentDAO = new CommentDAO();
$data = json_decode(file_get_contents("php://input"));

// Tu JS envía 'text' y 'rating' cuando edita
if(
    !empty($data->isbn) && 
    !empty($data->profileCode) && 
    !empty($data->text) && 
    !empty($data->rating)
) {
    // Creamos el Objeto Comment con los datos a actualizar
    $commentToUpdate = new Comment();
    $commentToUpdate->setIsbn($data->isbn);
    $commentToUpdate->setProfileCode($data->profileCode);
    $commentToUpdate->setCommentText($data->text);
    $commentToUpdate->setRating($data->rating);

    if($commentDAO->updateComment($commentToUpdate)) {
        http_response_code(200);
        echo json_encode(["message" => "Comment updated."]);
    } else {
        // Si no cambia nada (mismo texto) también puede dar false o 0 rows, 
        // pero devolvemos error genérico por seguridad.
        http_response_code(503);
        echo json_encode(["message" => "Unable to update comment."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>