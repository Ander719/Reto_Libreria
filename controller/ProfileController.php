<?php
require_once '../Config/Database.php';
require_once '../model/dao/ProfileDAO.php';

class ProfileController
{
    private $profileDAO;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->profileDAO = new ProfileDAO($db);
    }

    public function loginUser($username)
    {
        return $this->profileDAO->findLoginIdentityByUsername($username);
    }

    public function register($username, $passwordHash)
    {
        return $this->profileDAO->register($username, $passwordHash);
    }

    public function get_all_users()
    {
        return $this->profileDAO->get_all_users();
    }
    public function delete_user($id)
    {
        return $this->profileDAO->delete_user($id);
    }

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        return $this->profileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction);
    }

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        return $this->profileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    public function modifyPassword($profile_code, $passwordHash)
    {
        return $this->profileDAO->modifyPassword($profile_code, $passwordHash);
    }

    public function getProfile($id, $role) {
        return $this->profileDAO->getProfileByRole($id, $role);
    }

    public function isAdminByProfileCode($profileCode)
    {
        return $this->profileDAO->isAdminByProfileCode($profileCode);
    }
}
