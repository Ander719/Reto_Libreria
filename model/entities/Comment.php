<?php

// Resena de un usuario sobre un libro.
class Comment {
    private $profileCode;
    private $isbn;
    private $commentText;
    private $rating;
    private $dateComment;
    private $userName;

    // Se deja vacio porque la entidad se rellena con setters
    public function __construct() {}

    // Devuelve el codigo del perfil que hizo el comentario
    public function getProfileCode() {
        return $this->profileCode;
    }

    // Guarda el codigo del perfil que hizo el comentario
    public function setProfileCode($profileCode) {
        $this->profileCode = $profileCode;
    }

    // Devuelve el ISBN del libro comentado
    public function getIsbn() {
        return $this->isbn;
    }

    // Guarda el ISBN del libro comentado
    public function setIsbn($isbn) {
        $this->isbn = $isbn;
    }

    // Devuelve el texto de la resena
    public function getCommentText() {
        return $this->commentText;
    }

    // Guarda el texto de la resena
    public function setCommentText($commentText) {
        $this->commentText = $commentText;
    }

    // Devuelve la valoracion numerica
    public function getRating() {
        return $this->rating;
    }

    // Guarda la valoracion numerica
    public function setRating($rating) {
        $this->rating = $rating;
    }

    // Devuelve la fecha del comentario
    public function getDateComment() {
        return $this->dateComment;
    }

    // Guarda la fecha del comentario
    public function setDateComment($dateComment) {
        $this->dateComment = $dateComment;
    }

    // Devuelve el nombre publico del usuario
    public function getUserName() {
        return $this->userName;
    }

    // Guarda el nombre publico del usuario
    public function setUserName($userName) {
        $this->userName = $userName;
    }

    // Prepara el comentario para mandarlo como JSON con los nombres que usa el frontend
    public function toArray() {
        return [
            "profile_code" => $this->profileCode,
            "isbn"         => $this->isbn,       
            "comment_text" => $this->commentText,
            "valoration"   => $this->rating,     
            "dateComent"   => $this->dateComment,
            "user_name"    => $this->userName
        ];
    }
}
?>
