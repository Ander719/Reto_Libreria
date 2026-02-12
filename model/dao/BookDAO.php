<?php
// model/dao/BookDAO.php
require_once dirname(__DIR__, 2) . '/Config/Database.php';
require_once dirname(__DIR__) . '/entities/Book.php';
require_once __DIR__ . '/AuthorDAO.php';

class BookDAO {
    private $conn;
    private $AuthorDAO;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->AuthorDAO = new AuthorDAO();
    }

    public function getBookByIsbn($isbn) {
        $sql = "SELECT b.*, a.name_author, a.last_name 
                FROM book_ b 
                JOIN author_ a ON b.id_author = a.ID_AUTHOR 
                WHERE b.isbn = :isbn";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        $authorId = $this->AuthorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        if (!$authorId) return false;

        $query = "INSERT INTO BOOK_ (isbn, title, id_author, pages, stock, synopsis, price, editorial, cover) 
                  VALUES (:isbn, :title, :author, :pages, :stock, :synopsis, :price, :editorial, :cover)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":isbn" => $isbn, ":title" => $title, ":author" => $authorId,
            ":pages" => $pages, ":stock" => $stock, ":synopsis" => $synopsis,
            ":price" => $price, ":editorial" => $editorial, ":cover" => $coverName
        ]);
    }

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

    public function updateBook(Book $book) {
        $query = "UPDATE BOOK_ SET 
                    title = :title, 
                    id_author = :author,
                    pages = :pages, 
                    stock = :stock, 
                    synopsis = :synopsis, 
                    price = :price, 
                    editorial = :editorial, 
                    cover = :cover 
                WHERE isbn = :isbn";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":title" => $book->getTitle(),
            ":author" => $book->getAuthor(), 
            ":pages" => $book->getPages(),
            ":stock" => $book->getStock(),
            ":synopsis" => $book->getSynopsis(),
            ":price" => $book->getPrice(),
            ":editorial" => $book->getEditorial(),
            ":cover" => $book->getCover(),
            ":isbn" => $book->getIsbn()
        ]);
    }
}
?>