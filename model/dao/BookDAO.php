<?php
// model/dao/BookDAO.php
require_once dirname(__DIR__) . '/entities/Book.php';
require_once __DIR__ . '/AuthorDAO.php';

/**
 * Consultas de libros. Tambien se apoya en AuthorDAO cuando cambia el autor.
 */
class BookDAO {
    private $conn;
    private $authorDAO;

    /**
     * @param PDO $db Conexion PDO reutilizada por el DAO.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->authorDAO = new AuthorDAO($this->conn);
    }

    /**
     * Busca un libro y trae su autor en la misma consulta.
     *
     * @param string $isbn ISBN buscado.
     * @return Book|false Entidad encontrada o false.
     */
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

    /**
     * Inserta un libro nuevo. Si el autor no existe, se crea antes.
     *
     * @param string $isbn ISBN unico.
     * @param string $title Titulo.
     * @param string $authorName Nombre del autor.
     * @param string $authorSurname Apellido del autor.
     * @param int $pages Paginas.
     * @param int $stock Stock inicial.
     * @param string $synopsis Sinopsis.
     * @param float $price Precio.
     * @param string $editorial Editorial.
     * @param string $coverName Portada asociada.
     * @return bool True si se inserta correctamente.
     */
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

    /**
     * Lista el catalogo con la valoracion media calculada en SQL.
     *
     * @return array<int, array<string, mixed>> Libros serializados con campo rating.
     */
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

    /**
     * Actualiza el libro y cambia la referencia de autor si hace falta.
     *
     * @param string $isbn ISBN del libro existente.
     * @param string $title Titulo actualizado.
     * @param string $authorName Nombre del autor.
     * @param string $authorSurname Apellido del autor.
     * @param int $pages Paginas.
     * @param int $stock Stock actual.
     * @param string $synopsis Sinopsis.
     * @param float $price Precio.
     * @param string $editorial Editorial.
     * @param string $cover Portada a mantener o reemplazar.
     * @return bool True si la actualizacion se ejecuta.
     */
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
