<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new controller();
$type = $controller->checkUser($username, $password);
if ($type) {
    echo json_encode(["admin" => "admin"], JSON_UNESCAPED_UNICODE);
} else if (!$type) {
    echo json_encode(["user" => "user"], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => 'There was an error when processing the profile.'], JSON_UNESCAPED_UNICODE);
}
?>