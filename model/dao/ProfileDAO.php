<?php
require_once dirname(__DIR__) . '/entities/Admin.php';     // Sube 1 nivel hasta model y entra en entities
require_once dirname(__DIR__) . '/entities/User.php';      // Aseguramos que User también esté disponible

/**
 * Consultas y escrituras sobre perfiles, usuarios y administradores.
 */
class ProfileDAO
{
    private $conn;

    /**
     * @param PDO $db Conexion PDO reutilizada por el DAO.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }
    /**
     * Registra un usuario con el procedimiento de la base de datos.
     *
     * @param string $username Nombre de usuario.
     * @param string $password Hash de contrasena.
     * @return User|'ERROR_DUPLICADO'|'ERROR_BBDD'|'ERROR_SILENCIOSO' Resultado del alta.
     */
    public function register($username, $password)
    {
        try {
            // El procedimiento inserta en profile_ y user_ en el mismo flujo.
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
            // 23000 identifica violaciones de integridad, como username duplicado.
            if ($e->getCode() == '23000') {
                return "ERROR_DUPLICADO";
            }
            error_log("Error en ProfileDAO::register: " . $e->getMessage());
            return "ERROR_BBDD";
        }
    }

    /**
     * Busca un usuario normal por username.
     *
     * @param string $username Nombre de usuario.
     * @return User|null Usuario encontrado o null.
     */
    public function findUserByUsername($username)
    {
        // JOIN entre profile_ y user_ para hidratar la entidad concreta.
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

    /**
     * Busca un administrador por username.
     *
     * @param string $username Nombre de usuario.
     * @return Admin|null Administrador encontrado o null.
     */
    public function findAdminByUsername($username)
    {
        // JOIN entre profile_ y admin_ para distinguir la identidad de administracion.
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

    /**
     * Devuelve la identidad de login con su rol.
     *
     * @param string $username Nombre de usuario.
     * @return array{role:string,profile:User|Admin}|null Identidad autenticable o null.
     */
    public function findLoginIdentityByUsername($username)
    {
        $admin = $this->findAdminByUsername($username);
        if ($admin) {
            return ['role' => 'admin', 'profile' => $admin];
        }

        $user = $this->findUserByUsername($username);
        if ($user) {
            return ['role' => 'user', 'profile' => $user];
        }

        return null;
    }

    /**
     * Busca un usuario normal por codigo de perfil.
     *
     * @param int $id Codigo de perfil.
     * @return User|null Usuario encontrado o null.
     */
    public function getUserById($id)
    {
        // profile_ aporta los datos comunes; user_ aporta los datos de compra.
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
    /**
     * Busca un administrador por codigo de perfil.
     *
     * @param int $id Codigo de perfil.
     * @return Admin|null Administrador encontrado o null.
     */
    public function getAdminById($id)
    {
        // admin_ solo guarda el dato extra de cuenta corriente.
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
    }

    /**
     * Devuelve los usuarios normales que aparecen en el panel admin.
     *
     * @return User[] Usuarios encontrados.
     */
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


    /**
     * Guarda cambios de usuario en profile_ y user_.
     *
     * @param string|null $email Email actualizado.
     * @param string $username Nombre de usuario actualizado.
     * @param string|null $telephone Telefono actualizado.
     * @param string|null $name Nombre propio.
     * @param string|null $surname Apellidos.
     * @param string|null $gender Genero.
     * @param string|null $card_no Numero de tarjeta; si viene vacio se conserva el anterior.
     * @param int $profile_code Perfil afectado.
     * @param string|null $direction Direccion postal.
     * @return bool True si ambas actualizaciones se confirman.
     */
    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code, $direction)
    {
        try {
            // Son dos tablas. Si una falla, no interesa guardar la otra a medias.
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

            if ($card_no !== null) {
                $query2 = "UPDATE user_ SET 
                            gender = :gender, 
                            card_no = :card,
                            direction = :direction
                           WHERE profile_code = :code";
            } else {
                $query2 = "UPDATE user_ SET 
                            gender = :gender, 
                            direction = :direction
                           WHERE profile_code = :code";
            }

            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(":gender", $gender);
            if ($card_no !== null) {
                $stmt2->bindParam(":card", $card_no);
            }
            $stmt2->bindParam(":direction", $direction);
            $stmt2->bindParam(":code", $profile_code);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si falla algo, dejamos profile_ y user_ como estaban.
            error_log("Error en modifyUser: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Guarda cambios de administrador en profile_ y admin_.
     *
     * @param string|null $email Email actualizado.
     * @param string $username Nombre de usuario actualizado.
     * @param string|null $telephone Telefono actualizado.
     * @param string|null $name Nombre propio.
     * @param string|null $surname Apellidos.
     * @param string|null $current_account Cuenta corriente.
     * @param int $profile_code Perfil afectado.
     * @return bool True si ambas actualizaciones se confirman.
     */
    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {

        try {
            // Datos comunes y datos de admin se guardan juntos.
            $this->conn->beginTransaction();
            $name = !empty($name) ? $name : null;
            $surname = !empty($surname) ? $surname : null;
            $gender = !empty($gender) ? $gender : null;
            $email = !empty($email) ? $email : null;
            $telephone = !empty($telephone) ? $telephone : null;
            $current_account = !empty($current_account) ? $current_account : null;

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
            // Evita que admin_ y profile_ queden desincronizadas.
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Borra el perfil. La base de datos limpia las tablas dependientes.
     *
     * @param int $id Codigo de perfil.
     * @return bool True si la sentencia se ejecuta.
     */
    public function delete_user($id)
    {
        $query = "DELETE FROM profile_ WHERE profile_code = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Cambia el hash de contrasena de un perfil.
     *
     * @param int $profile_code Codigo de perfil.
     * @param string $password Hash generado previamente.
     * @return bool True si se ejecuta la actualizacion.
     */
    public function modifyPassword($profile_code, $password)
    {
        $query = "UPDATE profile_ SET pswd = :password WHERE profile_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":code", $profile_code);
        return $stmt->execute();
    }

    /**
     * Comprueba si un perfil pertenece a la tabla admin_.
     *
     * @param int $profileCode Codigo de perfil.
     * @return bool True si es administrador.
     */
    public function isAdminByProfileCode($profileCode)
    {
        $query = "SELECT profile_code FROM admin_ WHERE profile_code = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $profileCode);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    /**
     * Devuelve la entidad adecuada segun el rol de sesion.
     *
     * @param int $id Codigo de perfil.
     * @param string $role Rol (`admin` o `user`).
     * @return User|Admin|null Perfil encontrado.
     */
    public function getProfileByRole($id, $role)
    {
        return $role === 'admin' ? $this->getAdminById($id) : $this->getUserById($id);
    }
}
