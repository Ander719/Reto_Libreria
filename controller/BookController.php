<?php
// BookController.php — Controlador para la gestión de libros
// Actúa como un puente ciego entre la API y la capa de acceso a datos (DAO)
require_once '../model/dao/BookDAO.php';

class BookController {
    private $BookDAO;
    private $AuthorDAO;

    public function __construct($db) {
        $this->BookDAO = new BookDAO($db);
        $this->AuthorDAO = new AuthorDAO($db);
    }
    
    // Solicita un libro específico al DAO
    public function getBook($isbn) {
        return $this->BookDAO->getBookByIsbn($isbn);
    }

    // Solicita el listado completo de libros al DAO
    public function getAllBooks() {
        return $this->BookDAO->getAllBooks();
    }

    // Registra un nuevo libro. Recibe los parámetros ya validados y el ID del autor resuelto.
    public function createBook($isbn, $title, $pages, $stock, $synopsis, $price, $editorial, $coverName, $authorId) {
        return $this->BookDAO->createBookWithAuthor($isbn, $title, $pages, $stock, $synopsis, $price, $editorial, $coverName, $authorId);
    }

    // Actualiza la información de un libro existente en el DAO.
    public function modifyBook($book) {
        return $this->BookDAO->updateBook($book);
    }

    // Gestiona la obtención o creación de un autor delegando al DAO correspondiente.
    public function getOrCreateAuthor($name, $surname) {
        return $this->AuthorDAO->getOrCreateAuthorId($name, $surname);
    }
}
?>