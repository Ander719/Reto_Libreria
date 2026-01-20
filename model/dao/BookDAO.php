<?php
require_once dirname(__DIR__, 2) . '/Config/Database.php';
require_once dirname(__DIR__) . '/entities/Book.php';
require_once dirname(__DIR__) . '/entities/Author.php';
require_once __DIR__ . '/AuthorDAO.php'; // AuthorDAO está en la misma carpeta

class BookDAO
{
    private $conn;
    private $AuthorDAO;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->AuthorDAO = new AuthorDAO();
    }

    // --- LEER POR ISBN ---
    public function getBookByIsbn($isbn)
    {
        // Nota: Asegúrate que tu procedimiento almacenado devuelve 'name_author' y 'last_name'
        // Si no usas procedimientos, cambia esto por un SELECT con JOIN normal.
        $sql = "CALL GetBookByISBN(:isbn)";
        
        // Si no tienes el PROCEDURE, usa esta consulta alternativa:
        // $sql = "SELECT b.*, a.name_author, a.last_name FROM book_ b JOIN author_ a ON b.id_author = a.ID_AUTHOR WHERE b.isbn = :isbn";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // --- CREAR ---
    public function createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName)
    {
        $authorId = $this->AuthorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        if (!$authorId) return false;

        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $coverName);
        return $this->createBook($book);
    }

    public function createBook(Book $book)
    {
        $query = "INSERT INTO BOOK_ (isbn, title, id_author, pages, stock, synopsis, price, editorial, cover) 
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

        return $stmt->execute();
    }

    // --- ACTUALIZAR ---
    public function updateBook(Book $book)
    {
        // Solo actualizamos datos básicos. Si quieres cambiar el autor, necesitarías lógica extra.
        $query = "UPDATE BOOK_ SET 
                    title = :title, 
                    pages = :pages, 
                    stock = :stock, 
                    synopsis = :synopsis, 
                    price = :price, 
                    editorial = :editorial, 
                    cover = :cover 
                  WHERE isbn = :isbn";

        $stmt = $this->conn->prepare($query);

        $isbn = $book->getIsbn();
        $title = $book->getTitle();
        $pages = $book->getPages();
        $stock = $book->getStock();
        $synopsis = $book->getSynopsis();
        $price = $book->getPrice();
        $editorial = $book->getEditorial();
        $cover = $book->getCover();

        $stmt->bindParam(":isbn", $isbn);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":pages", $pages);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":synopsis", $synopsis);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":editorial", $editorial);
        $stmt->bindParam(":cover", $cover);

        return $stmt->execute();
    }
    
    // --- LISTAR TODOS ---
    public function getAllBooks() {
        $sql = "CALL GetAllBooks()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach ($rows as $row) {
            $bookObj = new Book(
                $row['title'],
                new Author($row['id_author'], $row['name_author'], $row['last_name']),
                $row['isbn'],
                $row['pages'],
                $row['stock'],
                $row['synopsis'],
                $row['price'],
                $row['editorial'],
                $row['cover']
            );
            $bookArray = $bookObj->toArray();
            $bookArray['rating'] = $row['rating'];
            $list[] = $bookArray;
        }
        return $list;
    }
}
?>