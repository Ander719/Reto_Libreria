<?php
require_once dirname(__DIR__, 2) . '/Config/Database.php'; // Sube 2 niveles hasta la raíz y entra en Config
require_once dirname(__DIR__) . '/entities/Admin.php';     // Sube 1 nivel hasta model y entra en entities
require_once dirname(__DIR__) . '/entities/User.php';      // Aseguramos que User también esté disponible

class ProfileDAO
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    public function register($username, $password)
    {
        try {
            $query = "CALL register_user(:username, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return new User(
                    $row['profile_code'],
                    $row['email'],
                    $row['user_name'],
                    $row['pswd'],
                    $row['telephone'],
                    $row['name_'],
                    $row['surname'],
                    $row['gender'],
                    $row['card_no']
                );
            }
            // Si llegamos aquí, SQL corrió pero no devolvió nada
            return "ERROR_SILENCIOSO";
        } catch (PDOException $e) {
            // ¡AQUÍ ESTÁ LA CLAVE!
            // El código 23000 es el estándar SQL para "Integrity Constraint Violation" (Duplicado)
            if ($e->getCode() == '23000') {
                return "ERROR_DUPLICADO";
            }

            // Cualquier otro error (conexión, etc)
            return "ERROR_BBDD: " . $e->getMessage();
        }
    }
    // ==========================================
    // 1. FUNCIONES DE LOGIN
    // ==========================================

    public function findUserByUsername($username)
    {
        // 1. Buscamos SOLO por nombre
        $sql = "SELECT * FROM profile_ p 
            JOIN user_ u ON p.profile_code = u.profile_code 
            WHERE p.user_name = :username";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'],
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['gender'],
                $row['card_no']
            );
        }
        return null;
    }

    public function findAdminByUsername($username)
    {
        // JOIN con la tabla de admins
        $sql = "SELECT * FROM profile_ p 
            JOIN admin_ a ON p.profile_code = a.profile_code 
            WHERE p.user_name = :username";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Retornamos una instancia de la clase Admin
            // El constructor depende de tu clase Admin.php: 
            // ($code, $email, $user, $pass, $tel, $name, $surname, $account)
            return new Admin(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'], // El hash
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['current_account']
            );
        }
        return null;
    }

    // ==========================================
    // 2. OBTENER DATOS
    // ==========================================

    public function getUserById($id)
    {
        $sql = "SELECT P.*, U.gender , U.card_no
                   FROM profile_ P 
                   JOIN user_ U ON P.profile_code = U.profile_code 
                   WHERE P.profile_code = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'],
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['gender'],
                $row['card_no']
            );
        }
        return null;
    }
    public function getAdminById($id)
    {
        $qAdmin = "SELECT P.*, A.current_account 
                   FROM profile_ P 
                   JOIN admin_ A ON P.profile_code = A.profile_code 
                   WHERE P.profile_code = :id";
        $stmt = $this->conn->prepare($qAdmin);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['role_type'] = 'admin'; // Añadimos marca de rol manualmente
            return $row;
        }

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Admin(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'], // El hash
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['current_account']
            );
        }
        return null;
    }

    public function get_all_users() {
        // CORREGIDO: Todo en minúsculas
        $query = "SELECT P.*, U.card_no, U.gender 
                  FROM profile_ P 
                  JOIN user_ U ON P.profile_code = U.profile_code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $list = [];

        foreach ($rows as $row) {
            $row['ROLE_TYPE'] = 'user';
            $list[] = $row;
        }
        return $list;
    }

    // ==========================================
    // 3. MODIFICAR DATOS (¡AQUÍ ESTABA EL FALLO!)
    // ==========================================

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Actualizar tabla PROFILE_ (Minúsculas: profile_, email, user_name, telephone, name_, surname)
            $query1 = "UPDATE profile_ SET 
                        email = :email, 
                        user_name = :username, 
                        telephone = :telephone, 
                        name_ = :name, 
                        surname = :surname 
                       WHERE profile_code = :code";
            
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(":email", $email);
            $stmt1->bindParam(":username", $username);
            $stmt1->bindParam(":telephone", $telephone);
            $stmt1->bindParam(":name", $name);
            $stmt1->bindParam(":surname", $surname);
            $stmt1->bindParam(":code", $profile_code);
            $stmt1->execute();

            // 2. Actualizar tabla USER_ (Minúsculas: user_, gender, card_no)
            $query2 = "UPDATE user_ SET 
                        gender = :gender, 
                        card_no = :card 
                       WHERE profile_code = :code";
            
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(":gender", $gender);
            $stmt2->bindParam(":card", $card_no);
            $stmt2->bindParam(":code", $profile_code);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Actualizar PROFILE_
            $query1 = "UPDATE profile_ SET 
                        email = :email, 
                        user_name = :username, 
                        telephone = :telephone, 
                        name_ = :name, 
                        surname = :surname 
                       WHERE profile_code = :code";
            
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(":email", $email);
            $stmt1->bindParam(":username", $username);
            $stmt1->bindParam(":telephone", $telephone);
            $stmt1->bindParam(":name", $name);
            $stmt1->bindParam(":surname", $surname);
            $stmt1->bindParam(":code", $profile_code);
            $stmt1->execute();

            // 2. Actualizar ADMIN_ (Minúsculas: admin_, current_account)
            $query2 = "UPDATE admin_ SET 
                        current_account = :account 
                       WHERE profile_code = :code";
            
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(":account", $current_account);
            $stmt2->bindParam(":code", $profile_code);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // ==========================================
    // 4. BORRAR Y CREAR
    // ==========================================

    public function delete_user($id) {
        // CORREGIDO: profile_ y profile_code
        $query = "DELETE FROM profile_ WHERE profile_code = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    public function modifyPassword($profile_code, $password) {
        $query = "UPDATE profile_ SET pswd = :password WHERE profile_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":code", $profile_code);
        return $stmt->execute();
    }
}
