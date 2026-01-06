<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/CommentModel.php';

$database = new Database();
$db = $database->getConnection();
$commentModel = new CommentModel($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->isbn) && 
    !empty($data->profileCode) && 
    !empty($data->text) && 
    !empty($data->rating)
) {
    $result = $commentModel->updateComment($data->isbn, $data->profileCode, $data->text, $data->rating);
    
    if($result !== false) {
        if ($result > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Comment updated."));
        } else {
            http_response_code(400); 
            echo json_encode(array(
                "message" => "Update failed: No changes made or record not found.",
                "debug" => "ISBN: [" . $data->isbn . "], Profile: [" . $data->profileCode . "]. Text: [" . $data->text . "]"
            ));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update comment (SQL Error)."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>