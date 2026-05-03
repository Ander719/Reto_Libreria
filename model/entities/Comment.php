<?php

class Comment {
    private $profileCode;
    private $isbn;
    private $commentText;
    private $rating;
    private $dateComment;
    private $userName;

    public function __construct() {}

    // --- GETTERS Y SETTERS ---

    public function getProfileCode() {
        return $this->profileCode;
    }

    public function setProfileCode($profileCode) {
        $this->profileCode = $profileCode;
    }

    public function getIsbn() {
        return $this->isbn;
    }

    public function setIsbn($isbn) {
        $this->isbn = $isbn;
    }

    public function getCommentText() {
        return $this->commentText;
    }

    public function setCommentText($commentText) {
        $this->commentText = $commentText;
    }

    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
    }

    public function getDateComment() {
        return $this->dateComment;
    }

    public function setDateComment($dateComment) {
        $this->dateComment = $dateComment;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function setUserName($userName) {
        $this->userName = $userName;
    }


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
