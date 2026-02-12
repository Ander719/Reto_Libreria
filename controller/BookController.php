<?php
// controller/BookController.php
require_once '../model/entities/Book.php';
require_once '../model/dao/BookDAO.php';
// AÑADIDO: Necesario para modifyBook
require_once '../model/dao/AuthorDAO.php'; 

class BookController {
    private $BookDAO;

    public function __construct() {
        $this->BookDAO = new BookDAO();
    }
    
    public function getBook($isbn) {
        // Sanitizar entrada
        $cleanIsbn = trim(htmlspecialchars($isbn));
        return $this->BookDAO->getBookByIsbn($cleanIsbn);
    }

    public function getAllBooks() {
        return $this->BookDAO->getAllBooks();
    }

    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        // 1. SANITIZACIÓN (Limpieza de código malicioso)
        $isbn          = trim(htmlspecialchars($isbn));
        $title         = trim(htmlspecialchars($title));
        $authorName    = trim(htmlspecialchars($authorName));
        $authorSurname = trim(htmlspecialchars($authorSurname));
        $synopsis      = trim(htmlspecialchars($synopsis));
        $editorial     = trim(htmlspecialchars($editorial));
        $coverName     = trim(htmlspecialchars($coverName));

        // 2. VALIDACIÓN DE TIPOS (Asegurar números)
        $pages = filter_var($pages, FILTER_VALIDATE_INT);
        $stock = filter_var($stock, FILTER_VALIDATE_INT);
        $price = filter_var($price, FILTER_VALIDATE_FLOAT);

        // 3. VALIDACIÓN LÓGICA
        if ($pages === false || $stock === false || $price === false) {
             return false; // O lanzar una excepción según tu gestión de errores
        }

        // Validación de campos obligatorios mínimos
        if (empty($isbn) || empty($title) || empty($authorName)) {
            return false;
        }
        $authorDAO = new AuthorDAO();
        $authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        
        if (!$authorId) return false;

        return $this->BookDAO->createBookWithAuthor($isbn, $title, $pages, $stock, $synopsis, $price, $editorial, $coverName, $authorId);
    }

    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        // 1. SANITIZACIÓN
        $isbn          = trim(htmlspecialchars($isbn));
        $title         = trim(htmlspecialchars($title));
        $authorName    = trim(htmlspecialchars($authorName));
        $authorSurname = trim(htmlspecialchars($authorSurname));
        $synopsis      = trim(htmlspecialchars($synopsis));
        $editorial     = trim(htmlspecialchars($editorial));
        $cover         = trim(htmlspecialchars($cover));

        // 2. VALIDACIÓN DE TIPOS
        $pages = filter_var($pages, FILTER_VALIDATE_INT);
        $stock = filter_var($stock, FILTER_VALIDATE_INT);
        $price = filter_var($price, FILTER_VALIDATE_FLOAT);

        if ($pages === false || $stock === false || $price === false) {
            return false;
        }

        // 3. Obtenemos o creamos el ID del autor
        $authorDAO = new AuthorDAO();
        $authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        
        if (!$authorId) return false;

        // 4. Creamos el objeto Book con el ID del autor
        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        
        // 5. Llamamos al DAO
        return $this->BookDAO->updateBook($book);
    }
}
?>