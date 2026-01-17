<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../controller/CommentController.php';

$commentModel = new CommentController();
$isbn = isset($_GET['isbn']) ? $_GET['isbn'] : "";
if($isbn) {
    $comments = $commentModel->getCommentsByISBN($isbn);
    echo json_encode($comments);
} else {
    echo json_encode([]);
}
?>