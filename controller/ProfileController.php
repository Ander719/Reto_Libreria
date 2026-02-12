<?php
require_once '../model/dao/ProfileDAO.php';

class ProfileController
{
    private $ProfileDAO;

    public function __construct()
    {
        $this->ProfileDAO = new ProfileDAO();
    }

    public function loginUser($username, $password)
    {
        // SANITIZACIÓN Y VALIDACIÓN
        $username = trim(htmlspecialchars($username));

        if (empty($username) || empty($password)) {
            return ["success" => false, "error" => "Datos vacíos", "status_code" => 400];
        }
        $admin = $this->ProfileDAO->findAdminByUsername($username);

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
        $user = $this->ProfileDAO->findUserByUsername($username); 

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
        // Saneamiento 
        $username = trim(htmlspecialchars($username));

        //  Validación de contraseña
        if (strlen($password) < 4) {
            return ["success" => false, "error" => "La contraseña debe tener al menos 4 caracteres"];
        }

        // ENCRIPTADO (Fundamental para password_verify)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

      
        // Pasamos el HASH, no la contraseña plana
        $resultado = $this->ProfileDAO->register($username, $passwordHash);

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
        return $this->ProfileDAO->get_all_users();
    }
    public function delete_user($id)
    {
        return $this->ProfileDAO->delete_user($id);
    }

public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
{
    // Sanitización
    $email = trim($email);
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    }
    $username = htmlspecialchars(trim($username));
    $name = htmlspecialchars(trim($name));
    $surname = htmlspecialchars(trim($surname));
    $telephone = filter_var($telephone, FILTER_SANITIZE_NUMBER_INT);
    $direction = htmlspecialchars(trim($direction));

    // Validación básica
    if (!empty($telephone) && strlen($telephone) !== 9) return false;
    if (!empty($card_no) && (strlen($card_no) !== 16 || !is_numeric($card_no))) return false;
    if (empty($profile_code)) return false;

    return $this->ProfileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction);
}

public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
{
    // Sanitización
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    $username = htmlspecialchars(trim($username));
    $name = htmlspecialchars(trim($name));
    $surname = htmlspecialchars(trim($surname));
    $current_account = htmlspecialchars(trim($current_account));

    // Validación
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    if (empty($current_account) || strlen($current_account) < 20) return false;
    if (empty($profile_code)) return false;

    return $this->ProfileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
}

    public function getProfile($id, $role) {
    
        if ($role === 'admin') {
            $data =$this->ProfileDAO->getAdminById($id);
            return $data ? $data->toArray() : null;
        } else {
            $data =$this->ProfileDAO->getUserById($id);
            return $data ? $data->toArray() : null;
        }
    }
}
