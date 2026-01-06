<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../Config/Database.php';
require_once '../model/BookModel.php';

$database = new Database();
$db = $database->getConnection();

$book = new BookModel($db);

$isbn = isset($_GET['isbn']) ? $_GET['isbn'] : die();

$stmt = $book->getBookByIsbn($isbn);
$num = $stmt->rowCount();

if ($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(
        array("message" => "Book not found.")
    );
}
?>
