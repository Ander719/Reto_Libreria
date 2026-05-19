<?php
// ProfileController.php — Controlador para la gestión de usuarios y perfiles
// Actúa como un puente ciego entre la API y la capa de acceso a datos (DAO)
require_once '../model/dao/ProfileDAO.php';

class ProfileController
{
    private $ProfileDAO;

    public function __construct($db)
    {
        $this->ProfileDAO = new ProfileDAO($db);
    }

    // Busca un administrador por su nombre de usuario
    public function findAdminByUsername($username)
    {
        return $this->ProfileDAO->findAdminByUsername($username);
    }

    // Busca un usuario estándar por su nombre de usuario
    public function findUserByUsername($username)
    {
        return $this->ProfileDAO->findUserByUsername($username);
    }

    // Registra un nuevo usuario en el sistema
    public function register($username, $passwordHash)
    {
        return $this->ProfileDAO->register($username, $passwordHash);
    }

    // Obtiene el listado completo de usuarios registrados
    public function get_all_users()
    {
        return $this->ProfileDAO->get_all_users();
    }

    // Elimina un usuario por su identificador único
    public function delete_user($id)
    {
        return $this->ProfileDAO->delete_user($id);
    }

    // Modifica los datos de un usuario estándar
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        return $this->ProfileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction);
    }

    // Modifica los datos de un administrador
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        return $this->ProfileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    // Obtiene un administrador por su ID
    public function getAdminById($id) {
        return $this->ProfileDAO->getAdminById($id);
    }

    // Obtiene un usuario estándar por su ID
    public function getUserById($id) {
        return $this->ProfileDAO->getUserById($id);
    }

    // Actualiza la contraseña del usuario
    public function modifyPassword($profile_code, $passwordHash)
    {
        return $this->ProfileDAO->modifyPassword($profile_code, $passwordHash);
    }
}
?>