<?php
require_once '../../config/Database.php';
require_once '../entities/Admin.php';

class ProfileDAO
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    public function createUser($username, $password)
    {
        // 1. ENCRIPTAR LA CONTRASEÑA
        // PASSWORD_DEFAULT usa el algoritmo bcrypt (el estándar actual)
        // Esto genera algo como: $2y$10$e4.K7X... (siempre diferente)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $query = "CALL register_user(:username, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);

            // 2. PASAMOS EL HASH, NO LA CONTRASEÑA PLANA
            $stmt->bindParam(":password", $passwordHash);

            $stmt->execute();

            // ... resto de tu código de fetch ...
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
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    // ==========================================
    // 1. FUNCIONES DE LOGIN (CRÍTICAS)
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

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
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
    // 2. OBTENER DATOS (PARA PERFIL Y TABLA)
    // ==========================================

    public function getUserById($id)
    {
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

    public function get_all_users()
    {
        $query = "SELECT P.*, U.CARD_NO, U.GENDER FROM PROFILE_ P JOIN USER_ U ON P.PROFILE_CODE = U.PROFILE_CODE";
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
    // 3. MODIFICAR DATOS (TRANSACCIONES)
    // ==========================================

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code)
    {
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

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
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

    public function delete_user($id)
    {
        $query = "DELETE FROM PROFILE_ WHERE PROFILE_CODE = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
