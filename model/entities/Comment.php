<?php

/**
 * Resena de un usuario sobre un libro.
 */
class Comment {
    private $profileCode;
    private $isbn;
    private $commentText;
    private $rating;
    private $dateComment;
    private $userName;

    /**
     * Se deja vacio porque la entidad se rellena con setters.
     */
    public function __construct() {}

    /** @return int Codigo del perfil autor del comentario. */
    public function getProfileCode() {
        return $this->profileCode;
    }

    /** @param int $profileCode Codigo del perfil autor. @return void */
    public function setProfileCode($profileCode) {
        $this->profileCode = $profileCode;
    }

    /** @return string ISBN comentado. */
    public function getIsbn() {
        return $this->isbn;
    }

    /** @param string $isbn ISBN comentado. @return void */
    public function setIsbn($isbn) {
        $this->isbn = $isbn;
    }

    /** @return string Texto de la resena. */
    public function getCommentText() {
        return $this->commentText;
    }

    /** @param string $commentText Texto de la resena. @return void */
    public function setCommentText($commentText) {
        $this->commentText = $commentText;
    }

    /** @return int Valoracion numerica. */
    public function getRating() {
        return $this->rating;
    }

    /** @param int $rating Valoracion numerica. @return void */
    public function setRating($rating) {
        $this->rating = $rating;
    }

    /** @return string Fecha del comentario. */
    public function getDateComment() {
        return $this->dateComment;
    }

    /** @param string $dateComment Fecha del comentario. @return void */
    public function setDateComment($dateComment) {
        $this->dateComment = $dateComment;
    }

    /** @return string Nombre publico del autor. */
    public function getUserName() {
        return $this->userName;
    }

    /** @param string $userName Nombre publico del autor. @return void */
    public function setUserName($userName) {
        $this->userName = $userName;
    }

    /**
     * Mantiene nombres antiguos como valoration y dateComent porque el JS los espera.
     *
     * @return array<string, mixed> Comentario listo para JSON.
     */
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
