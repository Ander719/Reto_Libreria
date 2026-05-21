<?php
require_once '../Config/Database.php';
require_once '../model/dao/ProfileDAO.php';

/**
 * Entrada de la capa API para trabajar con perfiles.
 */
class ProfileController
{
    private $profileDAO;

    /**
     * Prepara el DAO de perfiles.
     */
    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->profileDAO = new ProfileDAO($db);
    }

    /**
     * Busca una identidad de login por nombre de usuario.
     *
     * @param string $username Nombre de usuario introducido en login.
     * @return array{role:string,profile:User|Admin}|null Identidad con rol o null si no existe.
     */
    public function loginUser($username)
    {
        return $this->profileDAO->findLoginIdentityByUsername($username);
    }

    /**
     * Registra un usuario normal con la password ya hasheada.
     *
     * @param string $username Nombre unico de usuario.
     * @param string $passwordHash Hash generado con password_hash().
     * @return User|string Usuario creado o codigo de error del DAO.
     */
    public function register($username, $passwordHash)
    {
        return $this->profileDAO->register($username, $passwordHash);
    }

    /**
     * Devuelve los usuarios que puede ver el panel admin.
     *
     * @return array<int, array<string, mixed>> Usuarios y administradores serializados.
     */
    public function get_all_users()
    {
        return $this->profileDAO->get_all_users();
    }
    /**
     * Borra un perfil por codigo.
     *
     * @param int $id Codigo de perfil.
     * @return bool True si se elimina algun registro.
     */
    public function delete_user($id)
    {
        return $this->profileDAO->delete_user($id);
    }

    /**
     * Guarda datos comunes de perfil y datos propios de usuario.
     *
     * @param string $email Email actualizado.
     * @param string $username Nombre de usuario actualizado.
     * @param string $telephone Telefono actualizado.
     * @param string $name Nombre propio.
     * @param string $surname Apellidos.
     * @param string $gender Genero del usuario.
     * @param string $card_no Numero de tarjeta.
     * @param int $profile_code Perfil afectado.
     * @param string $direction Direccion postal.
     * @return bool True si la transaccion se completa.
     */
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        return $this->profileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction);
    }

    /**
     * Guarda datos comunes de perfil y datos propios de administrador.
     *
     * @param string $email Email actualizado.
     * @param string $username Nombre de usuario actualizado.
     * @param string $telephone Telefono actualizado.
     * @param string $name Nombre propio.
     * @param string $surname Apellidos.
     * @param string $current_account Cuenta corriente asociada.
     * @param int $profile_code Perfil afectado.
     * @return bool True si la transaccion se completa.
     */
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        return $this->profileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    /**
     * Cambia la contrasena de un perfil autenticado.
     *
     * @param int $profile_code Codigo de perfil.
     * @param string $passwordHash Nuevo hash de contrasena.
     * @return bool True si se actualiza la contrasena.
     */
    public function modifyPassword($profile_code, $passwordHash)
    {
        return $this->profileDAO->modifyPassword($profile_code, $passwordHash);
    }

    /**
     * Carga la entidad correcta usando el rol de sesion.
     *
     * @param int $id Codigo de perfil.
     * @param string $role Rol de sesion (`user` o `admin`).
     * @return User|Admin|null Perfil encontrado o null.
     */
    public function getProfile($id, $role) {
        return $this->profileDAO->getProfileByRole($id, $role);
    }

    /**
     * Comprueba si un codigo de perfil pertenece a un administrador.
     *
     * @param int $profileCode Codigo de perfil.
     * @return bool True si el perfil es administrador.
     */
    public function isAdminByProfileCode($profileCode)
    {
        return $this->profileDAO->isAdminByProfileCode($profileCode);
    }
}
