<?php
class BookModel
{
    private $conn;
    private $table_name = "BOOK_";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getBookByIsbn($isbn)
    {
        $query = "SELECT b.Isbn, b.title, b.pages, b.stock, b.sipnosis, b.price, b.editorial, b.cover, 
                         a.NameAuthor, a.LastName 
                  FROM " . $this->table_name . " b
                  LEFT JOIN AUTHOR_ a ON b.id_author = a.ID_AUTHOR
                  WHERE b.Isbn = :isbn 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $isbn = htmlspecialchars(strip_tags($isbn));

        $stmt->bindParam(":isbn", $isbn);

        $stmt->execute();

        return $stmt;
    }
}
?>
