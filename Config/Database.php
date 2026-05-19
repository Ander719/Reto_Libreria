<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $configFile = __DIR__ . '/db.env.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            $this->host = $config['host'] ?? 'localhost';
            $this->db_name = $config['db_name'] ?? 'CRUD_ADT';
            $this->username = $config['username'] ?? 'root';
            $this->password = $config['password'] ?? '';
        } else {
            // Fallback solo para desarrollo local
            $this->host = 'localhost';
            $this->db_name = 'CRUD_ADT';
            $this->username = 'root';
            $this->password = '';
        }
    }

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
            // Si falla la conexión, devolvemos un error 500 estandarizado
            error_log("Error de conexión BD: " . $e->getMessage());
            header("Content-Type: application/json; charset=utf-8");
            http_response_code(500);
            die(json_encode([
                "status" => "error",
                "code" => 500,
                "message" => "No se pudo establecer la conexión con la base de datos.",
                "data" => null
            ]));
        }
        return $this->conn;
    }
}
?>