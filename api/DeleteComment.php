<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';

$database = new Database();
$db = $database->getConnection();
$commentModel = new CommentDAO($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->isbn) && !empty($data->profileCode)) {
    if($commentModel->deleteComment($data->isbn, $data->profileCode)) {
        http_response_code(200);
        echo json_encode(array("message" => "Comment deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete comment (or permission denied/not found)."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>