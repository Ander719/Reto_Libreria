<?php
// controller/BookController.php
require_once '../Config/Database.php';
require_once '../model/dao/BookDAO.php';

class BookController {
    private $bookDAO;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->bookDAO = new BookDAO($db);
    }
    
    public function getBook($isbn) {
        return $this->bookDAO->getBookByIsbn($isbn);
    }

    public function getAllBooks() {
        return $this->bookDAO->getAllBooks();
    }

    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        return $this->bookDAO->createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        return $this->bookDAO->modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover);
    }
}
?>
