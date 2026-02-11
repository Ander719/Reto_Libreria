<?php
// api/AddComment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';
include_once '../model/entities/Comment.php';

error_reporting(0);
ini_set('display_errors', 0);

$commentDAO = new CommentDAO();
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->profileCode) &&
    !empty($data->isbn) &&
    !empty($data->text) &&    
    !empty($data->rating)     
) {
    $newComment = new Comment();
    $newComment->setProfileCode($data->profileCode);
    $newComment->setIsbn($data->isbn);
    
    $newComment->setCommentText($data->text); 
    $newComment->setRating($data->rating);
    
    $newComment->setDateComment($data->date ?? date('Y-m-d'));

    if ($commentDAO->createComment($newComment)) {
        http_response_code(201);
        echo json_encode(["message" => "Review posted successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to post review."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data. Expecting 'text' and 'rating'."]);
}
?>