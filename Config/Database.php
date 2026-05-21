<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'crud_adt';
    private $username = 'libreria_user';
    private $password = 'TuPass123!';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log('Error de conexión BD: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            die(json_encode([
                'status' => 'error',
                'code' => 500,
                'message' => 'Error interno de conexión a la base de datos',
                'data' => null
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $this->conn;
    }
}
?>
