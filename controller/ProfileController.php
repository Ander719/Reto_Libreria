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

    public function loginUser($username, $password)
    {
        $admin = $this->profileDAO->findAdminByUsername($username);

        // Si encontramos un admin, verificamos SU contraseña
        if ($admin && password_verify($password, $admin->getPswd())) {
            // Usamos toArray() que incluye el rol 'admin'
            $_SESSION['user'] = [
                'profile_code' => $admin->getProfile_code(),
                'user_name' => $admin->getUser_name(),
                'role' => 'admin'
            ];

            return ["success" => true, "role" => "admin" , "status_code" => 200];
        }

    //si no es admin, buscamos un user normal
        $user = $this->profileDAO->findUserByUsername($username); 

        if ($user && password_verify($password, $user->getPswd())) {
            $_SESSION['user'] = [
                'profile_code' => $user->getProfile_code(), 
                'user_name' => $user->getUser_name(),
                'role' => 'user'
            ];
            return ["success" => true, "role" => "user" , "status_code" => 200];
        }

    // Si no encontramos ni admin ni user, o la contraseña no coincide, devolvemos error genérico
        return ["success" => false, "error" => "Usuario o contraseña incorrectos", "status_code" => 401];
    }

    public function register($username, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

      
        // Pasamos el HASH, no la contraseña plana
        $resultado = $this->profileDAO->register($username, $passwordHash);

        if ($resultado instanceof User) {
            // Iniciar sesión automáticamente tras registro
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user'] = $resultado->toArray();
            return ["success" => true, "user" => $resultado->toArray()];
        }

       //  USUARIO DUPLICADO
        if ($resultado === "ERROR_DUPLICADO") {
            return ["success" => false, "error" => "Ese nombre de usuario ya está cogido."];
        }

        // otro error del sistema
        return ["success" => false, "error" => "Error del sistema: " . $resultado];
    }

    public function logout()
    {
        //  Iniciamos sesión solo si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        //Borramos las variables de sesión ($_SESSION['user'] = null)
        session_unset();

        // Destruimos la sesión completamente en el servidor
        session_destroy();

        return ["success" => true];
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
    
        if ($role === 'admin') {
            return $this->profileDAO->getAdminById($id);
        } else {
            return $this->profileDAO->getUserById($id);
        }
    }
}
