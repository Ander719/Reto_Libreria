<?php
require_once dirname(__DIR__) . '/entities/Admin.php';     // Sube 1 nivel hasta model y entra en entities
require_once dirname(__DIR__) . '/entities/User.php';      // Aseguramos que User también esté disponible

class ProfileDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
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
                    $row['card_no'],
                    $row['direction']
                );
            }

            return "ERROR_SILENCIOSO";
        } catch (PDOException $e) {
            // El código 23000 es el estándar SQL para "Integrity Constraint Violation" (Duplicado)
            if ($e->getCode() == '23000') {
                return "ERROR_DUPLICADO";
            }
            return "ERROR_BBDD: " . $e->getMessage();
        }
    }

    //login: busca por username y devuelve un objeto User o Admin dependiendo del tipo de perfil

    public function findUserByUsername($username)
    {

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
                $row['card_no'],
                $row['direction']
            );
        }
        return null;
    }

    public function findAdminByUsername($username)
    {

        $sql = "SELECT * FROM profile_ p 
            JOIN admin_ a ON p.profile_code = a.profile_code 
            WHERE p.user_name = :username";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            return new Admin(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'],
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['current_account']
            );
        }
        return null;
    }

    //obtener los datioas

    public function getUserById($id)
    {
        $sql = "SELECT * FROM profile_ P 
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
                $row['card_no'],
                $row['direction']
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

    public function get_all_users()
    {
        $query = "SELECT * FROM profile_ P 
                  JOIN user_ U ON P.profile_code = U.profile_code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $list = [];

        foreach ($rows as $row) {
            $list[] = new User(
                $row['profile_code'],
                $row['email'],
                $row['user_name'],
                $row['pswd'],
                $row['telephone'],
                $row['name_'],
                $row['surname'],
                $row['gender'],
                $row['card_no'],
                $row['direction']
            );
        }
        return $list;
    }


    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        try {
            $this->conn->beginTransaction();
            $name = !empty($name) ? $name : null;
            $surname = !empty($surname) ? $surname : null;
            $gender = !empty($gender) ? $gender : null;
            $email = !empty($email) ? $email : null;
            $telephone = !empty($telephone) ? $telephone : null;
            $card_no = !empty($card_no) ? $card_no : null;
            $direction = !empty($direction) ? $direction : null;

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

            $query2 = "UPDATE user_ SET 
                        gender = :gender, 
                        card_no = :card,
                        direction = :direction
                       WHERE profile_code = :code";

            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(":gender", $gender);
            $stmt2->bindParam(":card", $card_no);
            $stmt2->bindParam(":direction", $direction);
            $stmt2->bindParam(":code", $profile_code);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            error_log("Error en modifyUser: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {

        try {
            $this->conn->beginTransaction();
            $name = !empty($name) ? $name : null;
            $surname = !empty($surname) ? $surname : null;
            $gender = !empty($gender) ? $gender : null;
            $email = !empty($email) ? $email : null;
            $telephone = !empty($telephone) ? $telephone : null;
            $current_account = !empty($current_account) ? $current_account : null;

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

    // Eliminar usuario (solo perfil, el trigger se encarga de eliminar el detalle)


    public function delete_user($id)
    {
        // CORREGIDO: profile_ y profile_code
        $query = "DELETE FROM profile_ WHERE profile_code = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function modifyPassword($profile_code, $password)
    {
        $query = "UPDATE profile_ SET pswd = :password WHERE profile_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":code", $profile_code);
        return $stmt->execute();
    }

    public function isAdminByProfileCode($profileCode)
    {
        $query = "SELECT profile_code FROM admin_ WHERE profile_code = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $profileCode);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
