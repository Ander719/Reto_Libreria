<?php
require_once dirname(__DIR__, 2) . '/Config/Database.php';
class CommentDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createComment($profileCode, $isbn, $comment, $valoration, $date) {
        $query = "INSERT INTO comment_ (PROFILE_CODE, Isbn, comment_text, valoration, date_comment) 
                  VALUES (:profile, :isbn, :comment, :rating, :date)";
        $stmt = $this->conn->prepare($query);
        $comment = htmlspecialchars(strip_tags($comment));
        $stmt->bindParam(':profile', $profileCode);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':rating', $valoration);
        $stmt->bindParam(':date', $date);
        return $stmt->execute();
    }

    public function getCommentsByISBN($isbn) {
        // 1. Incluimos la clase Coment para poder crear los objetos
require_once dirname(__DIR__) . '/entities/Coment.php';
        $query = "SELECT c.PROFILE_CODE, 
                         c.comment_text, 
                         c.valoration, 
                         c.date_comment as dateComent, 
                         p.USER_NAME 
                  FROM comment_ c
                  JOIN profile_ p ON c.PROFILE_CODE = p.PROFILE_CODE
                  WHERE c.Isbn = :isbn
                  ORDER BY c.date_comment DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        
        // Obtenemos los datos crudos de la BD
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultArray = [];

        foreach ($rows as $row) {
            // 2. Guardamos los datos EN UN OBJETO (Instanciamos Coment)
            $comentObj = new Coment();
            
            // Usamos los Setters para "guardar" la información dentro del objeto
            $comentObj->setProfileCode($row['PROFILE_CODE']);
            $comentObj->setIsbn($isbn); // El ISBN ya lo tenemos
            $comentObj->setComent($row['comment_text']); // Mapeamos comment_text a la propiedad coment
            $comentObj->setValoration($row['valoration']);
            $comentObj->setDateComent($row['dateComent']);

            // 3. Obtenemos los datos A PARTIR DEL OBJETO para la respuesta
            // Usamos los Getters para extraer la info limpia del objeto
            $resultArray[] = [
                'PROFILE_CODE' => $comentObj->getProfileCode(),
                'comment_text' => $comentObj->getComent(), // Recuperado del objeto
                'valoration'   => $comentObj->getValoration(),
                'dateComent'   => $comentObj->getDateComent(),
                // USER_NAME viene de la tabla Profile (JOIN), no del objeto Coment, así que lo pasamos directo
                'USER_NAME'    => $row['USER_NAME']
            ];
        }
        
        return $resultArray;
    }

    public function deleteComment($isbn, $profileCode) {
        $query = "DELETE FROM comment_ WHERE Isbn = :isbn AND PROFILE_CODE = :profileCode";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':profileCode', $profileCode);
        if($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function updateComment($isbn, $profileCode, $text, $rating) {
        $query = "UPDATE comment_ SET comment_text = :text, valoration = :rating 
                  WHERE Isbn = :isbn AND PROFILE_CODE = :profileCode";
        $stmt = $this->conn->prepare($query);
        $text = htmlspecialchars(strip_tags($text));
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':profileCode', $profileCode);
        
        if($stmt->execute()) {
            return $stmt->rowCount();
        }
        return false;
    }
}
?>