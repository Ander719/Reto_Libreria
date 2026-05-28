<?php
// model/dao/BookDAO.php
require_once dirname(__DIR__) . '/entities/Book.php';
require_once __DIR__ . '/AuthorDAO.php';

// Consultas de libros, usa AuthorDAO para el autor.
class BookDAO {
    private $conn;
    private $authorDAO;

    // Guarda la conexion PDO y crea el AuthorDAO.
    public function __construct($db) {
        $this->conn = $db;
        $this->authorDAO = new AuthorDAO($this->conn);
    }

    // Busca un libro por ISBN y devuelve el objeto Book.
    public function getBookByIsbn($isbn) {
        // El JOIN evita otra consulta solo para completar el Author.
        $sql = "SELECT b.*, a.name_author, a.last_name 
                FROM book_ b 
                JOIN author_ a ON b.id_author = a.ID_AUTHOR 
                WHERE b.isbn = :isbn";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return new Book(
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
    }

    // Inserta un libro nuevo, creando el autor si hace falta.
    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        $authorId = $this->authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        if (!$authorId) {
            return false;
        }

        $query = "INSERT INTO book_ (isbn, title, id_author, pages, stock, synopsis, price, editorial, cover) 
                  VALUES (:isbn, :title, :author, :pages, :stock, :synopsis, :price, :editorial, :cover)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":isbn" => $isbn, ":title" => $title, ":author" => $authorId,
            ":pages" => $pages, ":stock" => $stock, ":synopsis" => $synopsis,
            ":price" => $price, ":editorial" => $editorial, ":cover" => $coverName
        ]);
    }

    // Obtiene todos los libros con su valoracion media.
    public function getAllBooks() {
        // GetAllBooks() deja la media de valoraciones en la base de datos.
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

    // Actualiza un libro y cambia el autor si es necesario.
    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        $authorId = $this->authorDAO->getOrCreateAuthorId($authorName, $authorSurname);
        if (!$authorId) {
            return false;
        }

        $query = "UPDATE book_ SET 
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
            ":title" => $title,
            ":author" => $authorId,
            ":pages" => $pages,
            ":stock" => $stock,
            ":synopsis" => $synopsis,
            ":price" => $price,
            ":editorial" => $editorial,
            ":cover" => $cover,
            ":isbn" => $isbn
        ]);
    }
}
?>
