<?php
require_once '../Config/Database.php';

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ==========================================
    // 1. FUNCIONES DE LOGIN
    // ==========================================
    
    public function loginUser($username, $password) {
        // CORREGIDO: Tablas y columnas en minúsculas (profile_, user_, user_name...)
        $query = "SELECT * FROM profile_ P 
                  JOIN user_ U ON P.profile_code = U.profile_code 
                  WHERE P.user_name = :username AND P.pswd = :pass";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":pass", $password);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function loginAdmin($username, $password) {
        // CORREGIDO: admin_ y columnas en minúsculas
        $query = "SELECT * FROM profile_ P 
                  JOIN admin_ A ON P.profile_code = A.profile_code 
                  WHERE P.user_name = :username AND P.pswd = :pass";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":pass", $password);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // 2. OBTENER DATOS
    // ==========================================

    public function getUserById($id) {
        // 1. Intentar Admin
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

        // 2. Intentar Usuario
        $qUser = "SELECT P.*, U.card_no, U.gender 
                  FROM profile_ P 
                  JOIN user_ U ON P.profile_code = U.profile_code 
                  WHERE P.profile_code = :id";
        $stmt = $this->conn->prepare($qUser);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['role_type'] = 'user';
            return $row;
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // 3. MODIFICAR DATOS (¡AQUÍ ESTABA EL FALLO!)
    // ==========================================

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code) {
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

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code) {
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
    
    public function create_user($username, $pswd) {
        // ... (Si usas el procedimiento almacenado register_user, asegúrate que existe)
        try {
            $query = "CALL register_user(:username, :pswd)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":pswd", $pswd);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function modifyPassword($profile_code, $password) {
        $query = "UPDATE profile_ SET pswd = :password WHERE profile_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":code", $profile_code);
        return $stmt->execute();
    }
}
?>