<?php
// controller/BookController.php
require_once '../Config/Database.php';
require_once '../model/dao/BookDAO.php';

// Punto intermedio entre la API de libros y BookDAO
class BookController {
    private $bookDAO;

    // Prepara el controlador con la conexion a BD
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->bookDAO = new BookDAO($db);
    }
    
    // Busca un libro por su ISBN
    public function getBook($isbn) {
        return $this->bookDAO->getBookByIsbn($isbn);
    }

    // Devuelve el catalogo completo de libros
    public function getAllBooks() {
        return $this->bookDAO->getAllBooks();
    }

    // Crea un libro y si el autor no existe lo crea tambien
    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        return $this->bookDAO->createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    // Guarda los cambios de un libro que ya existe
    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        return $this->bookDAO->modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover);
    }
}
?>
