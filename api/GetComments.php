<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';
$database = new Database();
$db = $database->getConnection();
$commentDAO = new CommentDAO($db);
$isbn = isset($_GET['isbn']) ? $_GET['isbn'] : "";
if($isbn) {
    $comments = $commentDAO->getCommentsByISBN($isbn);
    echo json_encode($comments);
} else {
    echo json_encode([]);
}
?>