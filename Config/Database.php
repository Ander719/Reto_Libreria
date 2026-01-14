<?php
class Database {
    private $host = "localhost";
    private $db_name = "CRUD_ADT";
    private $username = "root";
    private $password = "abcd*1234"; // <--- TU CONTRASEÑA RESTAURADA
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            // Esto permite ver errores de SQL si ocurren
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // IMPORTANTE: Si falla, detenemos todo y mostramos el error
            // Si quitamos esto, el resto de la web fallará silenciosamente
            header("Content-Type: application/json");
            die(json_encode(["error" => "Error de conexión BD: " . $e->getMessage()]));
        }
        return $this->conn;
    }
}
?>