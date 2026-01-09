<?php
// CORRECCIÓN: Apuntar a la carpeta 'Config' y al archivo 'Database.php'
require_once '../Config/Database.php'; 
require_once 'Book.php'; 

class BookDAO {
    private $conn;
    private $table_name = "Book_"; 

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // --- BUSCAR (READ) ---
    public function getBookByIsbn($isbn) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Isbn = :isbn LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Book(
                $row['title'],
                $row['id_author'], 
                $row['Isbn'],
                $row['pages'],
                $row['stock'],
                $row['sipnosis'], 
                $row['price'],
                $row['editorial'],
                $row['cover']
            );
        }
        return null;
    }

    // --- CREAR (INSERT) ---
    public function createBook($book) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET Isbn=:isbn, title=:title, id_author=:author, pages=:pages, 
                      stock=:stock, sipnosis=:synopsis, price=:price, editorial=:editorial, cover=:cover";

        $stmt = $this->conn->prepare($query);

        $val_isbn = $book->getIsbn();
        $val_title = $book->getTitle();
        $val_author = $book->getAuthor();
        $val_pages = $book->getPages();
        $val_stock = $book->getStock();
        $val_synopsis = $book->getSynopsis();
        $val_price = $book->getPrice();
        $val_editorial = $book->getEditorial();
        $val_cover = $book->getCover();

        $stmt->bindParam(":isbn", $val_isbn);
        $stmt->bindParam(":title", $val_title);
        $stmt->bindParam(":author", $val_author);
        $stmt->bindParam(":pages", $val_pages);
        $stmt->bindParam(":stock", $val_stock);
        $stmt->bindParam(":synopsis", $val_synopsis);
        $stmt->bindParam(":price", $val_price);
        $stmt->bindParam(":editorial", $val_editorial);
        $stmt->bindParam(":cover", $val_cover);

        return $stmt->execute();
    }

    // --- ACTUALIZAR (UPDATE) ---
    public function updateBook($book) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, id_author = :author, pages = :pages, stock = :stock, 
                      sipnosis = :synopsis, price = :price, editorial = :editorial, cover = :cover
                  WHERE Isbn = :isbn";

        $stmt = $this->conn->prepare($query);

        $val_isbn = $book->getIsbn();
        $val_title = $book->getTitle();
        $val_author = $book->getAuthor();
        $val_pages = $book->getPages();
        $val_stock = $book->getStock();
        $val_synopsis = $book->getSynopsis();
        $val_price = $book->getPrice();
        $val_editorial = $book->getEditorial();
        $val_cover = $book->getCover();

        $stmt->bindParam(":isbn", $val_isbn);
        $stmt->bindParam(":title", $val_title);
        $stmt->bindParam(":author", $val_author);
        $stmt->bindParam(":pages", $val_pages);
        $stmt->bindParam(":stock", $val_stock);
        $stmt->bindParam(":synopsis", $val_synopsis);
        $stmt->bindParam(":price", $val_price);
        $stmt->bindParam(":editorial", $val_editorial);
        $stmt->bindParam(":cover", $val_cover);

        return $stmt->execute();
    }

    // --- BORRAR (DELETE) ---
    public function deleteBook($isbn) {
        $query = "DELETE FROM " . $this->table_name . " WHERE Isbn = :isbn";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":isbn", $isbn);
        return $stmt->execute();
    }
}
?>