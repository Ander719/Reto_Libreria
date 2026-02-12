<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$controller = new CommentController();

$response = $controller->updateComment($data);

http_response_code($response["code"]);
echo json_encode([
    "success" => $response["success"],
    "message" => $response["message"]
]);?>