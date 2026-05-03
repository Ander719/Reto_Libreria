<?php
// controller/BookController.php
require_once '../Config/Database.php';
require_once '../model/entities/Book.php';
require_once '../model/dao/BookDAO.php';
require_once '../model/dao/AuthorDAO.php'; 

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
        $authorDAO = new AuthorDAO();
        $authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        
        if (!$authorId) return false;

        return $this->bookDAO->createBookWithAuthor($isbn, $title, $pages, $stock, $synopsis, $price, $editorial, $coverName, $authorId);
    }

    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        $authorDAO = new AuthorDAO();
        $authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        
        if (!$authorId) return false;

        // Creamos el objeto Book con el ID del autor
        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        
       
        return $this->bookDAO->updateBook($book);
    }
}
?>
