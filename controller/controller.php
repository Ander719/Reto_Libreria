<?php
require_once '../Config/Database.php';
require_once '../model/UserModel.php';
require_once '../model/bookDAO.php'; // <--- Incluimos el DAO de Libros
require_once '../model/Book.php'; // <--- Incluimos la clase Book

class controller
{
    private $UserModel;
    private $BookDAO; // <--- Añadimos propiedad para Libros

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        
        $this->UserModel = new UserModel($db);
        $this->BookDAO = new BookDAO(); // <--- Inicializamos el DAO de Libros
    }

    // --- FUNCIONES DE USUARIO EXISTENTES (No tocar) ---
    public function loginUser($username, $password) { return $this->UserModel->loginUser($username, $password); }
    public function loginAdmin($username, $password) { return $this->UserModel->loginAdmin($username, $password); }
    public function checkUser($username, $password) { return $this->UserModel->checkUser($username, $password); }
    public function create_user($username, $pswd1) { return $this->UserModel->create_user($username, $pswd1); }
    public function get_all_users() { return $this->UserModel->get_all_users(); }
    public function delete_user($id) { return $this->UserModel->delete_user($id); }
    public function modifyPassword($profile_code, $password) { return $this->UserModel->modifyPassword($profile_code, $password); }
    
    // Mantenemos tus funciones modifyUser y modifyAdmin tal cual...
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code) {
        return $this->UserModel->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);
    }
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code) {
        return $this->UserModel->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    // --- NUEVAS FUNCIONES PARA LIBROS ---

    // 1. Obtener libro por ISBN
    public function getBook($isbn) {
        return $this->BookDAO->getBookByIsbn($isbn);
    }

    // 2. Crear libro
    public function createBook($isbn, $title, $author, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        // Creamos el objeto Book aquí para pasarlo al DAO
        $book = new Book($title, $author, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        return $this->BookDAO->createBook($book);
    }

    // 3. Modificar libro
    public function modifyBook($isbn, $title, $author, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        $book = new Book($title, $author, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        return $this->BookDAO->updateBook($book);
    }

    // 4. Borrar libro
    public function deleteBook($isbn) {
        return $this->BookDAO->deleteBook($isbn);
    }
}
?>