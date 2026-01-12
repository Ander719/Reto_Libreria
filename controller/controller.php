<?php
require_once '../Config/Database.php';
require_once '../model/UserModel.php';
require_once '../model/bookDAO.php'; 

class controller {
    private $UserModel;
    private $BookDAO;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        
        $this->UserModel = new UserModel($db);
        $this->BookDAO = new BookDAO();
    }

    // --- FUNCIONES DE USUARIO ---
    public function loginUser($username, $password) { return $this->UserModel->loginUser($username, $password); }
    public function loginAdmin($username, $password) { return $this->UserModel->loginAdmin($username, $password); }
    public function checkUser($username, $password) { return $this->UserModel->checkUser($username, $password); }
    public function create_user($username, $pswd1) { return $this->UserModel->create_user($username, $pswd1); }
    public function get_all_users() { return $this->UserModel->get_all_users(); }
    public function delete_user($id) { return $this->UserModel->delete_user($id); }
    public function modifyPassword($profile_code, $password) { return $this->UserModel->modifyPassword($profile_code, $password); }
    
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code) {
        return $this->UserModel->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);
    }
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code) {
        return $this->UserModel->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    // --- NUEVO: NECESARIO PARA EL BOTÓN 'ADJUST DATA' ---
    public function getUserData($id) {
        // Esta función llama al modelo para obtener tus datos
        return $this->UserModel->getUserById($id);
    }

    // --- FUNCIONES DE LIBROS ---
    public function getBook($isbn) {
        return $this->BookDAO->getBookByIsbn($isbn);
    }

    // Modificado para soportar Autor Nombre/Apellido y Portada
    public function createBook($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName) {
        // Delegamos al DAO la lógica compleja de buscar/crear autor
        return $this->BookDAO->createBookWithAuthor($isbn, $title, $authorName, $authorSurname, $pages, $stock, $synopsis, $price, $editorial, $coverName);
    }

    public function modifyBook($isbn, $title, $authorId, $pages, $stock, $synopsis, $price, $editorial, $cover) {
        // Para modificar, usamos el ID directo si ya viene resuelto
        // Si necesitas modificar autor por nombre, habría que adaptar esto similar al create
        require_once '../model/Book.php'; 
        $book = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover);
        return $this->BookDAO->updateBook($book);
    }

    public function deleteBook($isbn) {
        return $this->BookDAO->deleteBook($isbn);
    }
}
?>