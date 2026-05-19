<?php
// model/dao/ProfileDAO.php — Capa de acceso a datos para perfiles de usuario y admin (usa PDO con sentencias preparadas)
require_once dirname(__DIR__, 2) . '/Config/Database.php'; // Sube 2 niveles hasta la raíz y entra en Config
require_once dirname(__DIR__) . '/entities/Admin.php';     // Sube 1 nivel hasta model y entra en entities
require_once dirname(__DIR__) . '/entities/User.php';      // Aseguramos que User también esté disponible

class ProfileDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo usuario usando procedimiento almacenado.
     * Usa sentencia preparada con bindParam para prevenir inyección SQL.
     */
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
            error_log("Error en ProfileDAO::register: " . $e->getMessage());
            return "ERROR_BBDD: " . $e->getMessage();
        }
    }

    /**
     * Busca un usuario por su nombre de usuario.
     * Usa sentencia preparada con bindParam.
     */
    public function findUserByUsername($username)
    {
        try {
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
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::findUserByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca un administrador por su nombre de usuario.
     * Usa sentencia preparada con bindParam.
     */
    public function findAdminByUsername($username)
    {
        try {
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
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::findAdminByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un usuario por su ID (profile_code).
     * Usa sentencia preparada con bindParam.
     */
    public function getUserById($id)
    {
        try {
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
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::getUserById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un administrador por su ID (profile_code).
     * Usa sentencia preparada con bindParam.
     */
    public function getAdminById($id)
    {
        try {
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
                    $row['pswd'],
                    $row['telephone'],
                    $row['name_'],
                    $row['surname'],
                    $row['current_account']
                );
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::getAdminById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los usuarios (no admins) del sistema.
     */
    public function get_all_users()
    {
        try {
            $query = "SELECT * FROM profile_ P 
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
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::get_all_users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Modifica los datos de un usuario (perfil + datos específicos de user_).
     * Usa transacción para garantizar atomicidad. Sentencias preparadas con bindParam.
     */
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
            error_log("Error en ProfileDAO::modifyUser: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Modifica los datos de un administrador (perfil + datos específicos de admin_).
     * Usa transacción para garantizar atomicidad. Sentencias preparadas con bindParam.
     */
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        try {
            $this->conn->beginTransaction();
            $name = !empty($name) ? $name : null;
            $surname = !empty($surname) ? $surname : null;
            $email = !empty($email) ? $email : null;
            $telephone = !empty($telephone) ? $telephone : null;
            $current_account = !empty($current_account) ? $current_account : null;

            // 1. Actualizar perfil base (profile_)
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

            // 2. Actualizar datos específicos del admin (admin_)
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
            error_log("Error en ProfileDAO::modifyAdmin: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Elimina un usuario por su profile_code.
     * Usa sentencia preparada con bindParam. El trigger de BD se encarga de eliminar el detalle.
     */
    public function delete_user($id)
    {
        try {
            $query = "DELETE FROM profile_ WHERE profile_code = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::delete_user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifica la contraseña de un usuario.
     * Usa sentencia preparada con bindParam. Recibe el hash ya generado por el controller.
     */
    public function modifyPassword($profile_code, $password)
    {
        try {
            $query = "UPDATE profile_ SET pswd = :password WHERE profile_code = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":code", $profile_code);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProfileDAO::modifyPassword: " . $e->getMessage());
            return false;
        }
    }
}
