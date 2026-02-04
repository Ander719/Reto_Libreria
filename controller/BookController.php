<?php
// controller/BookController.php
require_once '../model/entities/Book.php';
require_once '../model/dao/BookDAO.php';

class BookController {
    private $BookDAO;

    public function __construct() {
        $this->BookDAO = new BookDAO();
    }
    
    public function getBook($isbn) {
        return $this->BookDAO->getBookByIsbn($isbn);
    }

    public function getAllBooks() {
        return $this->BookDAO->getAllBooks();
    }

    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        return $this->BookDAO->createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    // Dentro de BookController.php
public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
    // 1. Obtenemos o creamos el ID del autor primero
    $authorDAO = new AuthorDAO();
    $authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
    
    if (!$authorId) return false;

    // 2. Creamos el objeto Book con el ID del autor
    $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
    
    // 3. Llamamos al DAO para hacer el UPDATE
    return $this->BookDAO->updateBook($book);
}
}
?>