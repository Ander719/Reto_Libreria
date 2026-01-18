<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';



$database = new Database();


$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->profileCode) &&
    !empty($data->isbn) &&
    !empty($data->comment) &&
    !empty($data->valoration)
) {
    if ($commentModel->createComment(
        $data->profileCode,
        $data->isbn,
        $data->comment,
        $data->valoration,
        $data->date
    )) {
        http_response_code(201);
        echo json_encode(array("message" => "Review posted successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to post review. SQL Error."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>