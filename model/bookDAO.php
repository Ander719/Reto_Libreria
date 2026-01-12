<?php
// model/BookDAO.php
require_once '../Config/Database.php';
require_once 'Book.php';
// Asegúrate de tener este archivo creado como te indiqué anteriormente
require_once 'AuthorDAO.php'; 

class BookDAO {
    private $conn;
    private $AuthorDAO;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->AuthorDAO = new AuthorDAO();
    }

    /**
     * Función principal para crear un libro gestionando el autor automáticamente
     */
    public function createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        
        // 1. Obtener ID del autor (existente o nuevo)
        $authorId = $this->AuthorDAO->getOrCreateAuthorId($authorName, $authorSurname);

        if (!$authorId) {
            return false; // Fallo al gestionar el autor
        }

        // 2. Crear el objeto Book
        // El constructor espera: $title, $author (id), $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover
        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $coverName);

        // 3. Insertar el libro usando la función de bajo nivel
        return $this->createBook($book);
    }

    /**
     * Inserta un objeto Book en la base de datos
     */
    public function createBook(Book $book) {
        $query = "INSERT INTO BOOK_ (ISBN, Title, ID_Author, NumPages, Stock, Synopsis, Price, Editorial, Cover) 
                  VALUES (:isbn, :title, :author, :pages, :stock, :synopsis, :price, :editorial, :cover)";
        
        $stmt = $this->conn->prepare($query);

        $isbn = $book->getIsbn();
        $title = $book->getTitle();
        $author = $book->getAuthor();
        $pages = $book->getPages();
        $stock = $book->getStock();
        $synopsis = $book->getSynopsis();
        $price = $book->getPrice();
        $editorial = $book->getEditorial();
        $cover = $book->getCover();

        $stmt->bindParam(":isbn", $isbn);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":author", $author);
        $stmt->bindParam(":pages", $pages);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":synopsis", $synopsis);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":editorial", $editorial);
        $stmt->bindParam(":cover", $cover);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Obtener libro por ISBN
     */
    public function getBookByIsbn($isbn) {
        $query = "SELECT * FROM BOOK_ WHERE ISBN = :isbn LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Reconstruimos el objeto Book
            return new Book(
                $row['Title'],
                $row['ID_Author'],
                $row['ISBN'],
                $row['NumPages'],
                $row['Stock'],
                $row['Synopsis'],
                $row['Price'],
                $row['Editorial'],
                $row['Cover']
            );
        }
        return null;
    }

    /**
     * Actualizar libro
     */
    public function updateBook(Book $book) {
        $query = "UPDATE BOOK_ SET 
                    Title = :title, 
                    ID_Author = :author, 
                    NumPages = :pages, 
                    Stock = :stock, 
                    Synopsis = :synopsis, 
                    Price = :price, 
                    Editorial = :editorial, 
                    Cover = :cover 
                  WHERE ISBN = :isbn";
        
        $stmt = $this->conn->prepare($query);

        $isbn = $book->getIsbn();
        $title = $book->getTitle();
        $author = $book->getAuthor();
        $pages = $book->getPages();
        $stock = $book->getStock();
        $synopsis = $book->getSynopsis();
        $price = $book->getPrice();
        $editorial = $book->getEditorial();
        $cover = $book->getCover();

        $stmt->bindParam(":isbn", $isbn);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":author", $author);
        $stmt->bindParam(":pages", $pages);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":synopsis", $synopsis);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":editorial", $editorial);
        $stmt->bindParam(":cover", $cover);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Eliminar libro
     */
    public function deleteBook($isbn) {
        $query = "DELETE FROM BOOK_ WHERE ISBN = :isbn";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":isbn", $isbn);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>