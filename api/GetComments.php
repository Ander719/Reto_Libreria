<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once '../controller/CommentController.php';

$controller = new CommentController();
$isbn = isset($_GET['isbn']) ? htmlspecialchars($_GET['isbn']) : "";

if($isbn) {
    echo json_encode($controller->getCommentsByISBN($isbn));
} else {
    echo json_encode([]);
}
?>