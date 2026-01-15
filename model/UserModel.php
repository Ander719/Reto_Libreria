<?php
require_once '../Config/Database.php';

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ==========================================
    // 1. FUNCIONES DE LOGIN (CRÍTICAS)
    // ==========================================
    
    public function loginUser($username, $password) {
        // Verifica si tus columnas se llaman USER_NAME o Username
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
    // 2. OBTENER DATOS (PARA PERFIL Y TABLA)
    // ==========================================

    public function getUserById($id) {
        // 1. Intentar Admin
        $qAdmin = "SELECT P.*, A.CURRENT_ACCOUNT 
                   FROM PROFILE_ P 
                   JOIN ADMIN_ A ON P.PROFILE_CODE = A.PROFILE_CODE 
                   WHERE P.PROFILE_CODE = :id";
        $stmt = $this->conn->prepare($qAdmin);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['ROLE_TYPE'] = 'admin'; 
            return $row;
        }

        // 2. Intentar Usuario
        $qUser = "SELECT P.*, U.CARD_NO, U.GENDER 
                  FROM PROFILE_ P 
                  JOIN USER_ U ON P.PROFILE_CODE = U.PROFILE_CODE 
                  WHERE P.PROFILE_CODE = :id";
        $stmt = $this->conn->prepare($qUser);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['ROLE_TYPE'] = 'user';
            return $row;
        }
        return null;
    }

    public function get_all_users() {
        $query = "SELECT P.*, U.CARD_NO, U.GENDER FROM PROFILE_ P JOIN USER_ U ON P.PROFILE_CODE = U.PROFILE_CODE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // 3. MODIFICAR DATOS (TRANSACCIONES)
    // ==========================================

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code) {
        try {
            $this->conn->beginTransaction();

            // Actualizar PROFILE_
            // NOTA: Si tu columna en BD es 'Name' (sin guion bajo), cambia NAME_ por Name aquí
            $query1 = "UPDATE PROFILE_ SET 
                        EMAIL = :email, 
                        USER_NAME = :username, 
                        TELEPHONE = :telephone, 
                        NAME_ = :name, 
                        SURNAME = :surname 
                       WHERE PROFILE_CODE = :code";
            
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(":email", $email);
            $stmt1->bindParam(":username", $username);
            $stmt1->bindParam(":telephone", $telephone);
            $stmt1->bindParam(":name", $name);
            $stmt1->bindParam(":surname", $surname);
            $stmt1->bindParam(":code", $profile_code);
            $stmt1->execute();

            // Actualizar USER_
            $query2 = "UPDATE USER_ SET 
                        GENDER = :gender, 
                        CARD_NO = :card 
                       WHERE PROFILE_CODE = :code";
            
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

            $query1 = "UPDATE PROFILE_ SET 
                        EMAIL = :email, 
                        USER_NAME = :username, 
                        TELEPHONE = :telephone, 
                        NAME_ = :name, 
                        SURNAME = :surname 
                       WHERE PROFILE_CODE = :code";
            
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(":email", $email);
            $stmt1->bindParam(":username", $username);
            $stmt1->bindParam(":telephone", $telephone);
            $stmt1->bindParam(":name", $name);
            $stmt1->bindParam(":surname", $surname);
            $stmt1->bindParam(":code", $profile_code);
            $stmt1->execute();

            $query2 = "UPDATE ADMIN_ SET 
                        CURRENT_ACCOUNT = :account 
                       WHERE PROFILE_CODE = :code";
            
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
        $query = "DELETE FROM PROFILE_ WHERE PROFILE_CODE = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    // Función de registro (si la usas en otro lado)
    // Sustituye la función create_user actual por esta:
    public function create_user($username, $pswd)
    {
        try {
            // 1. Verificar si el usuario ya existe
            $checkQuery = "SELECT * FROM PROFILE_ WHERE USER_NAME = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(1, $username);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return null; // El usuario ya existe
            }

            // 2. Llamar al procedimiento almacenado 'RegistrarUsuario'
            // Asegúrate de haber ejecutado el script SQL que crea este procedimiento
            $createQuery = "CALL register_user(?, ?)";
            $createStmt = $this->conn->prepare($createQuery);
            $createStmt->bindValue(1, $username);
            $createStmt->bindValue(2, $pswd);
            $createStmt->execute();

            // 3. Devolver el usuario creado (el procedimiento hace un SELECT al final)
            $result = $createStmt->fetch(PDO::FETCH_ASSOC);
            return $result;

        } catch (Exception $e) {
            // Puedes loguear el error si es necesario
            error_log("Error en create_user: " . $e->getMessage());
            return null;
        }
    }
}
?>