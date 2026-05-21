<?php
// controller/BookController.php
require_once '../Config/Database.php';
require_once '../model/dao/BookDAO.php';

/**
 * Punto intermedio entre la API de libros y BookDAO.
 */
class BookController {
    private $bookDAO;

    /**
     * Crea el DAO con la conexion de base de datos.
     */
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->bookDAO = new BookDAO($db);
    }
    
    /**
     * Busca un libro por su ISBN.
     *
     * @param string $isbn Identificador unico del libro.
     * @return Book|false Libro encontrado o false si no existe.
     */
    public function getBook($isbn) {
        return $this->bookDAO->getBookByIsbn($isbn);
    }

    /**
     * Devuelve el catalogo completo.
     *
     * @return array<int, array<string, mixed>> Libros preparados para serializar en JSON.
     */
    public function getAllBooks() {
        return $this->bookDAO->getAllBooks();
    }

    /**
     * Crea un libro. El DAO se encarga de reutilizar o crear el autor.
     *
     * @param string $isbn ISBN del libro.
     * @param string $title Titulo visible.
     * @param string $authorName Nombre del autor.
     * @param string $authorSurname Apellido del autor.
     * @param int $pages Numero de paginas.
     * @param int $stock Unidades disponibles.
     * @param string $synopsis Descripcion del libro.
     * @param float $price Precio actual.
     * @param string $editorial Editorial.
     * @param string $coverName Nombre de archivo de la portada.
     * @return bool True si se inserta correctamente.
     */
    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        return $this->bookDAO->createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    /**
     * Guarda cambios de un libro existente.
     *
     * @param string $isbn ISBN del libro a modificar.
     * @param string $title Titulo actualizado.
     * @param string $authorName Nombre del autor.
     * @param string $authorSurname Apellido del autor.
     * @param int $pages Numero de paginas.
     * @param int $stock Unidades disponibles.
     * @param string $synopsis Descripcion actualizada.
     * @param float $price Precio actual.
     * @param string $editorial Editorial.
     * @param string $cover Nombre de archivo de portada a conservar o reemplazar.
     * @return bool True si la actualizacion afecta al libro.
     */
    public function modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        return $this->bookDAO->modifyBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $cover);
    }
}
?>
