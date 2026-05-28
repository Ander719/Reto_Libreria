<?php
require_once '../Config/Database.php';
require_once '../model/dao/ProfileDAO.php';

// Entrada de la capa API para trabajar con perfiles
class ProfileController
{
    private $profileDAO;

    // Prepara el DAO de perfiles
    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->profileDAO = new ProfileDAO($db);
    }

    // Busca un usuario por su nombre para el login
    public function loginUser($username)
    {
        return $this->profileDAO->findLoginIdentityByUsername($username);
    }

    // Registra un usuario con la password ya hasheada
    public function register($username, $passwordHash)
    {
        return $this->profileDAO->register($username, $passwordHash);
    }

    // Devuelve todos los usuarios para el panel de admin
    public function get_all_users()
    {
        return $this->profileDAO->get_all_users();
    }
    // Borra un perfil por su codigo
    public function delete_user($id)
    {
        return $this->profileDAO->delete_user($id);
    }

    // Guarda los datos del perfil y del usuario normal
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        return $this->profileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction);
    }

    // Guarda los datos del perfil y del administrador
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        return $this->profileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    // Cambia la contrasena de un perfil
    public function modifyPassword($profile_code, $passwordHash)
    {
        return $this->profileDAO->modifyPassword($profile_code, $passwordHash);
    }

    // Carga el perfil segun el rol de la sesion
    public function getProfile($id, $role) {
        return $this->profileDAO->getProfileByRole($id, $role);
    }

    // Mira si un perfil es administrador por su codigo
    public function isAdminByProfileCode($profileCode)
    {
        return $this->profileDAO->isAdminByProfileCode($profileCode);
    }
}
