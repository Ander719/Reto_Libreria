<?php
require_once '../model/entities/Book.php';
require_once '../model/dao/BookDAO.php';

class BookController {
    private $BookDAO;

    public function __construct() {
        $this->BookDAO = new BookDAO();
    }
    // --- FUNCIONES DE LIBROS ---
    public function getBook($isbn) {
        return $this->BookDAO->getBookByIsbn($isbn);
    }
    public function getAllBooks() {
        return $this->BookDAO->getAllBooks();
    }

    // Modificado para soportar Autor Nombre/Apellido y Portada
    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        // Delegamos al DAO la lógica compleja de buscar/crear autor
        return $this->BookDAO->createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    public function modifyBook($isbn, $title, $authorId, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        // Para modificar, usamos el ID directo si ya viene resuelto
        // Si necesitas modificar autor por nombre, habría que adaptar esto similar al create
        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        return $this->BookDAO->updateBook($book);
    }

    public function deleteBook($isbn) {
        return $this->BookDAO->deleteBook($isbn);
    }
}