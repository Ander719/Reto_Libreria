<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$id = $_GET['id'] ?? '';

$controller = new controller();
$result = $controller->delete_user($id);

if ($result) {
    echo json_encode([
        'result' => TRUE
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>