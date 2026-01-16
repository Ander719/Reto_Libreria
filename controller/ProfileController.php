<?php
require_once '../model/dao/ProfileDAO.php';

class ProfileController {
    private $ProfileDAO;

    public function __construct() {
        $this->ProfileDAO = new ProfileDAO();
    }

    // --- FUNCIONES DE USUARIO ---
    public function loginUser($username, $password) {
         // 1. SANITIZACIÓN Y VALIDACIÓN (Rúbrica IL8.4)
        $username = trim(htmlspecialchars($username));
        
        if (empty($username) || empty($password)) {
            return ["success" => false, "error" => "Datos vacíos", "status_code" => 400];
        }
        // ---------------------------------------------------------
        // PASO A: INTENTAR COMO ADMIN (Prioridad)
        // ---------------------------------------------------------
        $admin = $this->ProfileDAO->findAdminByUsername($username);

        // Si encontramos un admin, verificamos SU contraseña
        if ($admin && password_verify($password, $admin->getPswd())) {
            session_start();
            // Usamos toArray() que incluye el rol 'admin'
            $_SESSION['user'] = $admin->toArray(); 
            
            return [
                "success" => true, 
                "role" => "admin", 
                "user" => $admin->toArray(),
                "status_code" => 200
            ];
        }

        // ---------------------------------------------------------
        // PASO B: INTENTAR COMO USUARIO NORMAL
        // ---------------------------------------------------------
        // (Asumiendo que renombramos el método anterior a findUserByUsername para ser claros)
        $user = $this->ProfileDAO->findUserByUsername($username); // O findByUsername si lo dejaste así

        if ($user && password_verify($password, $user->getPswd())) {
            session_start();
            $_SESSION['user'] = $user->toArray();
            
            return [
                "success" => true, 
                "role" => "user", 
                "user" => $user->toArray(),
                "status_code" => 200
            ];
        }

        // ---------------------------------------------------------
        // PASO C: FALLO
        // ---------------------------------------------------------
        return ["success" => false, "error" => "Usuario o contraseña incorrectos", "status_code" => 401];
    }

    public function register($username, $password) {
        // 1. Saneamiento (Rúbrica Seguridad)
        $username = trim(htmlspecialchars($username));
        
        // 2. Validación de contraseña
        if (strlen($password) < 4) {
            return ["success" => false, "error" => "La contraseña debe tener al menos 4 caracteres"];
        }

        // 3. ENCRIPTADO (Fundamental para password_verify)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 4. Llamada al DAO
        // Pasamos el HASH, no la contraseña plana
        $resultado = $this->ProfileDAO->register($username, $passwordHash);
        // CASO 1: ÉXITO (Es un objeto)
        if ($resultado instanceof User) { 
            // Iniciar sesión automáticamente tras registro (Opcional, pero recomendado)
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user'] = $resultado->toArray();
            return ["success" => true, "user" => $resultado->toArray()];
        }

        // CASO 2: FALLOS ESPECÍFICOS
        if ($resultado === "ERROR_DUPLICADO") {
            return ["success" => false, "error" => "Ese nombre de usuario ya está cogido."];
        }
        
        // CASO 3: OTROS FALLOS
        return ["success" => false, "error" => "Error del sistema: " . $resultado];
    }

     public function logout() {
        // 1. Iniciamos sesión solo si no está iniciada (para tener acceso a ella)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Borramos las variables de sesión ($_SESSION['user'] = null)
        session_unset();

        // 3. Destruimos la sesión completamente en el servidor
        session_destroy();

        return ["success" => true];
    }

    public function get_all_users() { return $this->ProfileDAO->get_all_users(); }
    public function delete_user($id) { return $this->ProfileDAO->delete_user($id); }
    //public function modifyPassword($profile_code, $password) { return $this->ProfileDAO->modifyPassword($profile_code, $password); }
    
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code) {
        return $this->ProfileDAO->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);
    }
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code) {
        return $this->ProfileDAO->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    // --- NUEVO: NECESARIO PARA EL BOTÓN 'ADJUST DATA' ---
    public function getUserData($id) {
        // Esta función llama al modelo para obtener tus datos
        return $this->ProfileDAO->getUserById($id);
    }
}